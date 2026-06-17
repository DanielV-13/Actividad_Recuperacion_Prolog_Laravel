<?php

namespace App\Http\Controllers;

use App\Services\PrologService;
use Illuminate\Http\Request;

/**
 * JuegoController
 *
 * Recibe la instruccion escrita en el chatbot, la interpreta, construye
 * la consulta Prolog correspondiente y devuelve la respuesta narrada.
 *
 * Gramatica de comandos soportada (separadores en minuscula):
 *   personajes
 *   enemigos
 *   misiones
 *   armas
 *   inventario <Nombre>
 *   acepta <Nombre> en <idMision>
 *   ataque <Nombre1, Nombre2, ...> vs <Enemigo>
 *   reporte <Nombre1, Nombre2, ...> en <idMision>
 *   ayuda
 */
class JuegoController extends Controller
{
    public function __construct(private PrologService $prolog) {}

    public function index(Request $request)
    {
        // El historial vive en la sesion para simular una conversacion.
        $historial = $request->session()->get('historial', []);

        return view('juego.chatbot', ['historial' => $historial]);
    }

    public function consultar(Request $request)
    {
        $request->validate(['mensaje' => 'required|string|max:200']);

        $mensaje   = trim($request->input('mensaje'));
        $respuesta = $this->interpretar($mensaje);

        // Guardamos el intercambio en el historial de la sesion.
        $historial   = $request->session()->get('historial', []);
        $historial[] = ['usuario' => $mensaje, 'bot' => $respuesta];
        $request->session()->put('historial', $historial);

        return redirect()->route('juego.index');
    }

    public function limpiar(Request $request)
    {
        $request->session()->forget('historial');

        return redirect()->route('juego.index');
    }

    /**
     * Traduce la instruccion en lenguaje casi natural a un objetivo Prolog.
     */
    private function interpretar(string $mensaje): string
    {
        $texto = mb_strtolower($mensaje);

        // --- Listados simples ---
        if ($this->esComando($texto, 'personajes')) {
            return $this->prolog->consultar(
                "forall(personaje(N,Niv,V), format('~w (nivel ~w, vida ~w)~n',[N,Niv,V]))"
            );
        }
        if ($this->esComando($texto, 'enemigos')) {
            return $this->prolog->consultar(
                "forall(enemigo(N,F,V), format('~w [~w] - vida ~w~n',[N,F,V]))"
            );
        }
        if ($this->esComando($texto, 'misiones')) {
            return $this->prolog->consultar(
                "forall(mision(ID,Nom,Dif,XP), format('~w: ~w (dificultad ~w, XP ~w)~n',[ID,Nom,Dif,XP]))"
            );
        }
        if ($this->esComando($texto, 'armas')) {
            return $this->prolog->consultar(
                "forall(arma(A,P), format('~w -> ~w pts de ataque~n',[A,P]))"
            );
        }
        if ($this->esComando($texto, 'ayuda') || $texto === '?') {
            return $this->ayuda();
        }

        // --- inventario <Nombre> ---
        if (str_starts_with($texto, 'inventario')) {
            $nombre = trim(mb_substr($mensaje, mb_strlen('inventario')));
            if ($nombre === '') {
                return 'Uso: inventario <Nombre>. Ej: inventario Kratos';
            }
            $n = $this->atomo($nombre);
            return $this->prolog->consultar(
                "(inventario({$n}, L) -> "
                . "(atomic_list_concat(L, ', ', S), format('Inventario de {$nombre}: ~w~n',[S])) "
                . "; writeln('Ese personaje no existe.'))"
            );
        }

        // --- acepta <Nombre> en <idMision> ---
        if (str_starts_with($texto, 'acepta')) {
            $resto = trim(mb_substr($mensaje, mb_strlen('acepta')));
            [$nombre, $mision] = $this->separar($resto, ' en ');
            if ($nombre === '' || $mision === '') {
                return 'Uso: acepta <Nombre> en <idMision>. Ej: acepta Elara en m2';
            }
            $n   = $this->atomo($nombre);
            $mid = $this->idMision($mision);
            return $this->prolog->consultar(
                "(puede_aceptar({$n}, {$mid}) -> "
                . "format('Si: {$nombre} puede aceptar la mision {$mision}.~n',[]) "
                . "; format('No: {$nombre} no tiene nivel para la mision {$mision}.~n',[]))"
            );
        }

        // --- ataque <grupo> vs <Enemigo> ---
        if (str_starts_with($texto, 'ataque')) {
            $resto = trim(mb_substr($mensaje, mb_strlen('ataque')));
            [$grupoTxt, $enemigo] = $this->separar($resto, ' vs ');
            if ($grupoTxt === '' || $enemigo === '') {
                return 'Uso: ataque <Nombre1, Nombre2, ...> vs <Enemigo>. '
                     . 'Ej: ataque Kratos, Nathan Drake vs Valkyria';
            }
            $grupo = $this->listaAtomos($grupoTxt);
            $en    = $this->atomo($enemigo);
            return $this->prolog->consultar(
                "(ejecutar_ataque({$grupo}, {$en}, M) -> writeln(M) "
                . "; writeln('No se pudo resolver el ataque. Revisa nombres y enemigo.'))"
            );
        }

        // --- reporte <grupo> en <idMision> ---
        if (str_starts_with($texto, 'reporte')) {
            $resto = trim(mb_substr($mensaje, mb_strlen('reporte')));
            [$grupoTxt, $mision] = $this->separar($resto, ' en ');
            if ($grupoTxt === '' || $mision === '') {
                return 'Uso: reporte <Nombre1, Nombre2, ...> en <idMision>. '
                     . 'Ej: reporte Elara, Rin en m2';
            }
            $grupo = $this->listaAtomos($grupoTxt);
            $mid   = $this->idMision($mision);
            return $this->prolog->consultar(
                "(generar_reporte_grupo({$grupo}, {$mid}, M) -> writeln(M) "
                . "; writeln('No se pudo generar el reporte.'))"
            );
        }

        return "No entendi la instruccion. Escribe \"ayuda\" para ver los comandos disponibles.";
    }

    // ----------------------------------------------------------------
    //  Utilidades
    // ----------------------------------------------------------------

    /** Coincidencia exacta de comando de una sola palabra. */
    private function esComando(string $texto, string $comando): bool
    {
        return $texto === $comando;
    }

    /** Separa "A <sep> B" en [A, B]. Si no hay separador, [resto, '']. */
    private function separar(string $texto, string $sep): array
    {
        $pos = mb_stripos($texto, $sep);
        if ($pos === false) {
            return [trim($texto), ''];
        }
        $a = trim(mb_substr($texto, 0, $pos));
        $b = trim(mb_substr($texto, $pos + mb_strlen($sep)));
        return [$a, $b];
    }

    /**
     * Convierte un nombre a un atomo Prolog entre comillas simples.
     * Conserva mayusculas/minusculas (los hechos usan 'Elara', 'Kratos', etc.)
     * y elimina comillas para evitar romper la consulta.
     */
    private function atomo(string $valor): string
    {
        $limpio = str_replace(["'", "\n", "\r"], '', trim($valor));
        return "'" . $limpio . "'";
    }

    /** Convierte "A, B, C" en una lista Prolog ['A','B','C']. */
    private function listaAtomos(string $texto): string
    {
        $partes = array_filter(array_map('trim', explode(',', $texto)), fn($p) => $p !== '');
        $atomos = array_map(fn($p) => $this->atomo($p), $partes);
        return '[' . implode(', ', $atomos) . ']';
    }

    /**
     * Normaliza el id de mision. Si el usuario escribe "m2" se usa como atomo
     * sin comillas (es un atomo valido en minuscula). Cualquier otra cosa se
     * limpia igual.
     */
    private function idMision(string $valor): string
    {
        $limpio = str_replace(["'", "\n", "\r", ' '], '', mb_strtolower(trim($valor)));
        return $limpio === '' ? "''" : $limpio;
    }

    private function ayuda(): string
    {
        return "Comandos disponibles:\n"
             . "- personajes\n"
             . "- enemigos\n"
             . "- misiones\n"
             . "- armas\n"
             . "- inventario <Nombre>            (ej: inventario Kratos)\n"
             . "- acepta <Nombre> en <idMision>  (ej: acepta Elara en m2)\n"
             . "- ataque <Grupo> vs <Enemigo>    (ej: ataque Kratos, Rin vs Valkyria)\n"
             . "- reporte <Grupo> en <idMision>  (ej: reporte Elara, Rin en m2)";
    }
}

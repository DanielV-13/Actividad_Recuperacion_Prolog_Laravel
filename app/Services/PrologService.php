<?php

namespace App\Services;

/**
 * PrologService
 *
 * Puente entre Laravel y la base de conocimiento en SWI-Prolog.
 *
 * Estrategia: en lugar de pasar la consulta como argumento por linea de
 * comandos (lo que genera problemas de comillas en Windows/Linux), se
 * escribe un pequeno script .pl temporal que carga la base de conocimiento
 * (juego.pl) y ejecuta el objetivo. Asi solo necesitamos escapar la ruta
 * del archivo, no el contenido de la consulta.
 */
class PrologService
{
    /**
     * Ejecuta un objetivo (goal) Prolog sobre la base de conocimiento y
     * devuelve lo que el objetivo imprima por salida estandar.
     *
     * El objetivo recibido DEBE encargarse de imprimir su resultado
     * (writeln/format), porque solo se captura el stdout de SWI-Prolog.
     */
    public function consultar(string $goal): string
    {
        // Ruta a la base de conocimiento. Prolog usa "/" incluso en Windows.
        $kb = str_replace('\\', '/', base_path('prolog/juego.pl'));

        // Script temporal: carga la KB y ejecuta el objetivo de forma segura.
        // Usamos initialization/2 en modo "main": SWI ejecuta main como punto de
        // entrada y termina solo (sin necesidad de halt manual). Esto evita los
        // warnings "Initialization goal called halt" y "use initialization/2".
        $script = ":- set_prolog_flag(encoding, utf8).\n"
                . ":- initialization(main, main).\n"
                . "main :- consult('{$kb}'),\n"
                . "        ( ({$goal}) -> true ; writeln('Sin resultados') ).\n";

        // Guardamos el script en storage/app (siempre escribible por Laravel).
        $tmp = storage_path('app/consulta_' . uniqid() . '.pl');
        file_put_contents($tmp, $script);
        $tmpPosix = str_replace('\\', '/', $tmp);

        // Binario de SWI-Prolog. Configurable por .env (SWIPL_PATH).
        // Por defecto "swipl" si esta en el PATH del sistema.
        $swipl = env('SWIPL_PATH', 'swipl');

        $cmd = escapeshellarg($swipl)
             . ' -q '
             . escapeshellarg($tmpPosix)
             . ' 2>&1';

        $salida = shell_exec($cmd);

        @unlink($tmp);

        $salida = trim((string) $salida);

        if ($salida === '') {
            return 'Sin resultados.';
        }

        // Si SWI-Prolog no esta instalado / no se encuentra el binario.
        if (stripos($salida, 'not found') !== false
            || stripos($salida, 'no se reconoce') !== false
            || stripos($salida, 'is not recognized') !== false) {
            return 'No se encontro SWI-Prolog. Verifica que este instalado y '
                 . 'configura SWIPL_PATH en el archivo .env.';
        }

        return $salida;
    }
}

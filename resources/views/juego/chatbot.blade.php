<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RPG Prolog Bot - Daniel Vaca</title>

    {{-- Tipografias pixeladas: Press Start 2P para titulos y etiquetas,
         VT323 para los textos largos del chat (mas legible en parrafos). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">

    <style>
        /* Paleta inspirada en las ventanas de menu de Dragon Quest (era SNES):
           azul intenso de fondo, marco blanco con contorno negro y dorado para
           resaltar lo importante. */
        :root {
            --azul-ventana:     #1c2db0;
            --azul-ventana-osc: #0b1352;
            --azul-campo:       #0b1352;
            --marco:            #ffffff;
            --oro:              #ffd23f;
            --texto:            #ffffff;
            --texto-suave:      #bcd0ff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            /* Fondo pixel art con un velo oscuro encima para que las ventanas y el
               texto sigan legibles. Si la imagen no existe, queda el color #05061a.
               Coloca tu imagen en: public/images/fondo.png */
            background:
                linear-gradient(rgba(5,6,26,.72), rgba(5,6,26,.88)),
                url('/images/fondo.png') center center / cover fixed,
                #05061a;
            color: var(--texto);
            font-family: 'VT323', monospace;
            font-size: 22px;
            line-height: 1.15;
            image-rendering: pixelated;   /* mantiene nitido el pixel art al escalar */
        }

        .lienzo {
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        /* ----------------------------- Cabecera ----------------------------- */
        .titulo {
            font-family: 'Press Start 2P', monospace;
            font-size: 26px;
            text-align: center;
            letter-spacing: 1px;
            text-shadow: 3px 3px 0 #000, 0 0 12px rgba(80,120,255,.6);
            margin: 18px 0 22px;
        }

        /* ------------- Ventana base estilo Dragon Quest (doble marco) ------------- */
        .ventana {
            background: linear-gradient(180deg, var(--azul-ventana) 0%, var(--azul-ventana-osc) 100%);
            border: 4px solid var(--marco);
            border-radius: 12px;
            /* Contorno negro por fuera + linea interior tenue = el marco clasico. */
            box-shadow: 0 0 0 4px #000, inset 0 0 0 3px rgba(255,255,255,.18);
            padding: 18px 20px;
        }
        .seccion { margin-top: 18px; }

        /* --------------------------- Conversacion --------------------------- */
        .chat {
            height: 430px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding-right: 6px;
        }
        .chat::-webkit-scrollbar { width: 10px; }
        .chat::-webkit-scrollbar-thumb { background: #fff; }
        .chat::-webkit-scrollbar-track { background: rgba(0,0,0,.3); }

        .burbuja {
            max-width: 88%;
            padding: 8px 12px;
            border: 3px solid #fff;
            border-radius: 8px;
            white-space: pre-line;   /* respeta los saltos de linea de Prolog */
            font-size: 21px;
        }
        .burbuja-bot {
            align-self: flex-start;
            background: rgba(0,0,0,.35);
        }
        .burbuja-usuario {
            align-self: flex-end;
            background: rgba(255,210,63,.14);
            border-color: var(--oro);
        }
        .quien {
            display: block;
            font-family: 'Press Start 2P', monospace;
            font-size: 10px;
            margin-bottom: 6px;
            opacity: .9;
        }
        .burbuja-bot .quien { color: var(--oro); }

        /* ------------------------- Menu de comandos ------------------------- */
        .menu-titulo {
            font-family: 'Press Start 2P', monospace;
            font-size: 12px;
            color: var(--oro);
            margin: 0 0 14px;
        }
        .comandos {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 18px;
        }
        .cmd {
            position: relative;
            background: none;
            border: none;
            color: #fff;
            font-family: 'VT323', monospace;
            font-size: 21px;
            text-align: left;
            padding: 4px 4px 4px 26px;
            cursor: pointer;
        }
        /* Cursor triangular del menu, dibujado con CSS (nada de emojis). */
        .cmd::before {
            content: "";
            position: absolute;
            left: 6px; top: 50%;
            transform: translateY(-50%);
            border-style: solid;
            border-width: 7px 0 7px 11px;
            border-color: transparent transparent transparent var(--oro);
            opacity: 0;
        }
        .cmd:hover, .cmd:focus { color: var(--oro); outline: none; }
        .cmd:hover::before, .cmd:focus::before { opacity: 1; }

        /* ------------------------- Entrada de texto ------------------------- */
        .fila-envio { display: flex; gap: 12px; margin-top: 18px; }
        .campo {
            flex: 1;
            background: var(--azul-campo);
            border: 4px solid #fff;
            border-radius: 10px;
            box-shadow: 0 0 0 4px #000;
            color: #fff;
            font-family: 'VT323', monospace;
            font-size: 22px;
            padding: 10px 14px;
        }
        .campo::placeholder { color: #7f8fd0; }
        .campo:focus { outline: none; border-color: var(--oro); }

        .boton {
            font-family: 'Press Start 2P', monospace;
            font-size: 12px;
            background: linear-gradient(180deg, #2a3ad0, #101a7a);
            color: #fff;
            border: 4px solid #fff;
            border-radius: 10px;
            box-shadow: 0 0 0 4px #000;
            padding: 0 20px;
            cursor: pointer;
        }
        .boton:hover { background: linear-gradient(180deg, #3a4af0, #1a249a); }
        .boton:active { transform: translateY(2px); }

        .error { color: #ff8080; font-size: 20px; margin-top: 10px; }

        .limpiar {
            display: block;
            margin: 12px 0 0 auto;
            color: #9fb0e0;
            font-size: 18px;
            text-decoration: underline;
            background: none;
            border: none;
            cursor: pointer;
        }
        .limpiar:hover { color: #fff; }

        /* ------------- Paneles laterales (roster fijo de unidades) ------------- */
        .rail {
            position: fixed;
            top: 96px;
            width: 215px;
            max-height: calc(100vh - 130px);
            overflow-y: auto;
            z-index: 5;
        }
        .rail-izq { left: 24px; }
        .rail-der { right: 24px; }
        .rail::-webkit-scrollbar { width: 8px; }
        .rail::-webkit-scrollbar-thumb { background: #fff; }
        .rail::-webkit-scrollbar-track { background: rgba(0,0,0,.3); }

        .unidad {
            padding: 7px 2px;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }
        .unidad:last-child { border-bottom: none; }
        .unidad .nombre {
            font-size: 23px;
            letter-spacing: .5px;
            line-height: 1.1;
        }
        .unidad .stat {
            font-size: 16px;
            color: #9fb0e0;
            margin-top: 2px;
        }

        /* En pantallas angostas ocultamos los paneles para no apretar el chat. */
        @media (max-width: 1300px) {
            .rail { display: none; }
        }
    </style>
</head>
<body>
    {{-- Panel izquierdo: roster de personajes (nombre a dos colores) --}}
    <aside class="ventana rail rail-izq">
        <p class="menu-titulo">PERSONAJES</p>
        @php
            // Cada heroe lleva su color principal (c) y el secundario del contorno (s).
            $heroes = [
                ['n' => 'Elara',           'nv' => 5, 'hp' => 100, 'c' => '#4fd6c9', 's' => '#ffffff'],
                ['n' => 'Kael',            'nv' => 3, 'hp' => 80,  'c' => '#7ec850', 's' => '#234d23'],
                ['n' => 'Rin',             'nv' => 7, 'hp' => 120, 'c' => '#c08bff', 's' => '#ffd23f'],
                ['n' => 'Kratos',          'nv' => 7, 'hp' => 150, 'c' => '#ff4d4d', 's' => '#ffffff'],
                ['n' => 'Nathan Drake',    'nv' => 5, 'hp' => 90,  'c' => '#9aa86a', 's' => '#d8c4a0'],
                ['n' => 'Crash Bandicoot', 'nv' => 2, 'hp' => 50,  'c' => '#ff7a18', 's' => '#1f6fff'],
            ];
        @endphp
        @foreach ($heroes as $h)
            <div class="unidad">
                <div class="nombre" style="color: {{ $h['c'] }}; text-shadow: 2px 2px 0 {{ $h['s'] }};">{{ $h['n'] }}</div>
                <div class="stat">Nv {{ $h['nv'] }} &middot; {{ $h['hp'] }} HP</div>
            </div>
        @endforeach
    </aside>

    {{-- Panel derecho: enemigos (Valkyria en dorado) --}}
    <aside class="ventana rail rail-der">
        <p class="menu-titulo">ENEMIGOS</p>
        @php
            $enemigos = [
                ['n' => 'Valkyria',   'fr' => 'God of War', 'hp' => 300, 'c' => '#ffd23f', 's' => '#8a6d0b'],
                ['n' => 'Mercenario', 'fr' => 'Uncharted',  'hp' => 80,  'c' => '#cfd6e0', 's' => '#3a4150'],
                ['n' => 'Jabali',     'fr' => 'Crash',      'hp' => 25,  'c' => '#d08a3a', 's' => '#5a3210'],
            ];
        @endphp
        @foreach ($enemigos as $e)
            <div class="unidad">
                <div class="nombre" style="color: {{ $e['c'] }}; text-shadow: 2px 2px 0 {{ $e['s'] }};">{{ $e['n'] }}</div>
                <div class="stat">{{ $e['hp'] }} HP &middot; {{ $e['fr'] }}</div>
            </div>
        @endforeach
    </aside>

    <div class="lienzo">

        <h1 class="titulo">RPG PROLOG BOT</h1>

        {{-- Ventana de conversacion --}}
        <div class="ventana">
            <div class="chat" id="chat">
                <div class="burbuja burbuja-bot">
                    <span class="quien">JARVIS</span>Bienvenido, aventurero. Preguntame por personajes, misiones, inventarios o ejecuta ataques. Escribe "ayuda" para ver los comandos.
                </div>

                @foreach ($historial as $turno)
                    {{-- Mensaje del usuario --}}
                    <div class="burbuja burbuja-usuario">
                        <span class="quien">TU</span>{{ $turno['usuario'] }}
                    </div>
                    {{-- Respuesta del bot --}}
                    <div class="burbuja burbuja-bot">
                        <span class="quien">JARVIS</span>{{ $turno['bot'] }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Menu de comandos: cada opcion rellena el campo de texto --}}
        <div class="ventana seccion">
            <p class="menu-titulo">COMANDOS</p>
            @php
                $ejemplos = [
                    'personajes', 'enemigos', 'misiones', 'armas',
                    'nivel Kratos',
                    'inventario Kratos',
                    'acepta Elara en m2',
                    'ataque Kratos, Nathan Drake vs Valkyria',
                    'reporte Elara, Rin en m2',
                    'ayuda',
                ];
            @endphp
            <div class="comandos">
                @foreach ($ejemplos as $ej)
                    <button type="button" class="cmd"
                            onclick="document.getElementById('mensaje').value='{{ $ej }}'">
                        {{ $ej }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Formulario de envio --}}
        <form action="{{ route('juego.consultar') }}" method="POST" class="fila-envio">
            @csrf
            <input id="mensaje" name="mensaje" autocomplete="off" autofocus
                   class="campo" placeholder="Escribe una instruccion...">
            <button type="submit" class="boton">ENVIAR</button>
        </form>

        @error('mensaje')
            <p class="error">{{ $message }}</p>
        @enderror

        {{-- Limpiar conversacion --}}
        <form action="{{ route('juego.limpiar') }}" method="POST">
            @csrf
            <button type="submit" class="limpiar">Limpiar conversacion</button>
        </form>
    </div>

    {{-- Auto-scroll al final del chat --}}
    <script>
        const chat = document.getElementById('chat');
        chat.scrollTop = chat.scrollHeight;
    </script>
</body>
</html>

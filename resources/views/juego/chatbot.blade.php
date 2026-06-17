<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RPG Prolog Bot - Daniel Vaca</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <div class="max-w-2xl mx-auto p-4">

        <header class="text-center my-6">
            <h1 class="text-3xl font-bold text-emerald-400">🎮 RPG Prolog Bot</h1>
            <p class="text-slate-400 text-sm mt-1">
                Chatbot conectado a una base de conocimiento en SWI-Prolog ·
                Actividad de Recuperacion · Daniel Vaca
            </p>
        </header>

        {{-- Ventana de conversacion --}}
        <div class="bg-slate-800 rounded-xl shadow-lg p-4 h-[420px] overflow-y-auto flex flex-col gap-3"
             id="chat">
            <div class="bg-slate-700/60 text-slate-200 rounded-lg p-3 text-sm self-start max-w-[85%]">
                ¡Hola! Soy tu maestro del juego. Preguntame por personajes, misiones,
                inventarios o ejecuta ataques. Escribe <b>ayuda</b> para ver los comandos.
            </div>

            @foreach ($historial as $turno)
                {{-- Mensaje del usuario --}}
                <div class="bg-emerald-600 text-white rounded-lg p-3 text-sm self-end max-w-[85%] whitespace-pre-line">{{ $turno['usuario'] }}</div>
                {{-- Respuesta del bot --}}
                <div class="bg-slate-700/60 text-slate-100 rounded-lg p-3 text-sm self-start max-w-[85%] whitespace-pre-line font-mono">{{ $turno['bot'] }}</div>
            @endforeach
        </div>

        {{-- Botones de ejemplo (rellenan el campo de texto) --}}
        <div class="flex flex-wrap gap-2 mt-3 text-xs">
            @php
                $ejemplos = [
                    'personajes', 'enemigos', 'misiones', 'armas',
                    'inventario Kratos',
                    'acepta Elara en m2',
                    'ataque Kratos, Nathan Drake vs Valkyria',
                    'reporte Elara, Rin en m2',
                    'ayuda',
                ];
            @endphp
            @foreach ($ejemplos as $ej)
                <button type="button"
                        onclick="document.getElementById('mensaje').value='{{ $ej }}'"
                        class="bg-slate-700 hover:bg-slate-600 rounded-full px-3 py-1">
                    {{ $ej }}
                </button>
            @endforeach
        </div>

        {{-- Formulario de envio --}}
        <form action="{{ route('juego.consultar') }}" method="POST" class="mt-3 flex gap-2">
            @csrf
            <input id="mensaje" name="mensaje" autocomplete="off" autofocus
                   placeholder="Escribe una instruccion..."
                   class="flex-1 bg-slate-800 border border-slate-600 rounded-lg px-4 py-2
                          focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <button type="submit"
                    class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold
                           rounded-lg px-5">
                Enviar
            </button>
        </form>

        @error('mensaje')
            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
        @enderror

        {{-- Limpiar conversacion --}}
        <form action="{{ route('juego.limpiar') }}" method="POST" class="mt-2 text-right">
            @csrf
            <button type="submit" class="text-slate-500 hover:text-slate-300 text-xs underline">
                Limpiar conversacion
            </button>
        </form>
    </div>

    {{-- Auto-scroll al final del chat --}}
    <script>
        const chat = document.getElementById('chat');
        chat.scrollTop = chat.scrollHeight;
    </script>
</body>
</html>

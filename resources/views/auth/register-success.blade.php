<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Solicitud Enviada • Gestior</title>

  {{-- Fuentes --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --bg-deep-1:#0b1020; --bg-deep-2:#0f172a;
    }
    body{ font-family:'Inter',sans-serif; letter-spacing:-0.012rem; }

    .abstract-bg{
      position: fixed; inset:0; z-index:-1;
      background:
        radial-gradient(1200px 600px at 90% 110%, rgba(37,99,235,.22), transparent 60%),
        radial-gradient(900px 500px at -10% -20%, rgba(91,33,182,.24), transparent 60%),
        linear-gradient(180deg, var(--bg-deep-2) 0%, var(--bg-deep-1) 100%);
    }

    .card{
      border-radius: 1.5rem; overflow: hidden;
      box-shadow: 0 30px 80px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.04) inset;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      backdrop-filter: blur(2px);
    }

    @keyframes checkmark {
      0% { stroke-dashoffset: 100; }
      100% { stroke-dashoffset: 0; }
    }

    .checkmark {
      stroke-dasharray: 100;
      animation: checkmark 0.8s ease-in-out;
    }
  </style>
</head>
<body class="h-full">
  <div class="abstract-bg"></div>

  <div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg">

      {{-- Logo --}}
      <div class="text-center mb-8">
        <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-16 w-auto mx-auto select-none" />
      </div>

      <div class="card bg-white p-8 md:p-10 text-center">

        {{-- Success Icon --}}
        <div class="mb-6 flex justify-center">
          <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 48 48">
              <circle cx="24" cy="24" r="22" stroke-width="3" fill="none" stroke="#10b981" opacity="0.2"/>
              <path class="checkmark" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                    d="M14 24l8 8 16-16" stroke="#10b981" fill="none"/>
            </svg>
          </div>
        </div>

        {{-- Mensaje Principal --}}
        <h1 class="text-3xl font-bold text-slate-900 mb-3">
          ¡Solicitud enviada!
        </h1>

        <p class="text-slate-600 mb-6">
          {{ session('message', 'Tu solicitud de acceso ha sido enviada correctamente y será atendida tan pronto como sea posible.') }}
        </p>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-8 text-left">
          <div class="flex gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-blue-800">
              <p class="font-semibold mb-2">¿Qué sigue ahora?</p>
              <ul class="space-y-2 text-blue-700">
                <li class="flex items-start gap-2">
                  <span class="flex-shrink-0">1.</span>
                  <span>Nuestro equipo revisará tu solicitud en las próximas <strong>24 horas</strong></span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="flex-shrink-0">2.</span>
                  <span>Te enviaremos un correo con tus <strong>credenciales de acceso</strong></span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="flex-shrink-0">3.</span>
                  <span>Podrás comenzar a usar <strong>Gestior inmediatamente</strong></span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
          <a href="{{ route('login') }}"
             class="block w-full py-3 px-4 rounded-md text-white bg-slate-900 hover:bg-black font-semibold transition-all duration-200 shadow-lg">
            Ir a inicio de sesión
          </a>

          <a href="{{ route('plans') }}"
             class="block w-full py-3 px-4 rounded-md text-slate-700 bg-slate-100 hover:bg-slate-200 font-medium transition-all duration-200">
            Ver otros planes
          </a>
        </div>

        {{-- Extra Info --}}
        <div class="mt-8 pt-6 border-t border-slate-200">
          <p class="text-sm text-slate-500">
            ¿No recibiste el email? Revisa tu carpeta de spam o
            <a href="mailto:soporte@gestior.com" class="text-violet-700 hover:underline font-medium">contacta con soporte</a>
          </p>
        </div>
      </div>

      <div class="text-center mt-6">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gestior — Todos los derechos reservados.</p>
      </div>
    </div>
  </div>
</body>
</html>

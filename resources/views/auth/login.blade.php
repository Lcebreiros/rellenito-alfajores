<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Iniciar sesión • Gestior</title>

  {{-- Fuentes --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --bg-deep-1:#0b1020; --bg-deep-2:#0f172a; --stroke: rgba(168,85,247,.22);
    }
    body{ font-family:'Inter',sans-serif; letter-spacing:-0.012rem; }
    [x-cloak]{display:none!important}

    /* Fondo abstracto degradé (detrás de todo) */
    .abstract-bg{
      position: fixed; inset:0; z-index:-1;
      background:
        radial-gradient(1200px 600px at 90% 110%, rgba(37,99,235,.22), transparent 60%),
        radial-gradient(900px 500px at -10% -20%, rgba(91,33,182,.24), transparent 60%),
        linear-gradient(180deg, var(--bg-deep-2) 0%, var(--bg-deep-1) 100%);
    }
    .abstract-bg::before,
    .abstract-bg::after{
      content:""; position:absolute; inset:0; pointer-events:none;
    }
    .abstract-bg::before{
      opacity:.10;
      background:
        radial-gradient(220px 220px at 15% 20%, #60a5fa 10%, transparent 60%),
        radial-gradient(180px 180px at 85% 75%, #a78bfa 10%, transparent 60%),
        radial-gradient(140px 140px at 55% 35%, #7dd3fc 10%, transparent 60%);
      mix-blend-mode: screen;
    }
    .abstract-bg::after{
      opacity:.07;
      background:
        radial-gradient(300px 300px at 30% 90%, #8b5cf6 10%, transparent 60%),
        radial-gradient(180px 180px at 75% 10%, #60a5fa 10%, transparent 60%);
      filter: blur(6px);
    }

    .card{
      border-radius: 1.5rem; overflow: hidden;
      box-shadow: 0 30px 80px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.04) inset;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      backdrop-filter: blur(2px);
    }
    .brand-pane{
      position: relative; background:#0b1220; color:#fff; isolation:isolate;
    }
    .brand-pane::after{
      content:""; position:absolute; inset:0; opacity:.6; pointer-events:none;
      background: linear-gradient(115deg, transparent 0 48%, rgba(255,255,255,.04) 48.2% 48.6%, transparent 49% 100%);
    }
    .brand-pane::before{
      content:""; position:absolute; inset:0; pointer-events:none; z-index:0;
      background:
        linear-gradient(to bottom, rgba(148,163,184,.08) 1px, transparent 1px) 0 0/100% 24px,
        linear-gradient(to right, rgba(148,163,184,.065) 1px, transparent 1px) 0 0/24px 100%,
        radial-gradient(60% 140% at 100% 0%, rgba(124,58,237,.10), transparent 70%),
        radial-gradient(80% 120% at 0% 100%, rgba(37,99,235,.08), transparent 65%);
      mask-image: linear-gradient(180deg, rgba(0,0,0,.9), rgba(0,0,0,.6));
    }

    @media (min-width:768px){
      .split-stroke{ position:relative; }
      .split-stroke::before{
        content:""; position:absolute; top:0; bottom:0; left:-0.5px; width:1px;
        background: linear-gradient(180deg, transparent, var(--stroke), transparent);
      }
    }

    .focus-ring:focus{ box-shadow:0 0 0 3px rgba(124,58,237,.16); outline:none; }
    .txt{ transition: box-shadow .18s ease, transform .08s ease; }
    .btn{ transition: transform .18s ease, box-shadow .18s ease, background-color .2s; }
    .btn:hover{ transform: translateY(-1px); }
    .btn:active{ transform: translateY(0); }
  </style>

  @livewireStyles
</head>
<body class="h-full">
  <div class="abstract-bg"></div>

  <div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-6xl">
      <div class="card grid grid-cols-1 md:grid-cols-2">

        {{-- IZQUIERDA: Branding Gestior --}}
        <section class="brand-pane p-8 md:p-12 flex items-center">
          <div class="relative z-10 w-full text-center md:text-left">
            <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-28 md:h-32 w-auto select-none" />
            <h1 class="mt-6 text-4xl md:text-5xl font-extrabold tracking-tight">¡Hola, bienvenido!</h1>
            <p class="mt-3 text-slate-300/95 text-sm md:text-base">
              Toda la gestión que necesitas, en un solo lugar
            </p>
          </div>
        </section>

        {{-- DERECHA: Formulario --}}
        <section class="bg-white text-slate-900 p-8 md:p-12 split-stroke">
          <div class="max-w-md mx-auto">
            <header class="mb-6">
              <h2 class="text-2xl font-semibold tracking-tight">Acceder</h2>
              <p class="text-sm text-slate-500 mt-1">Usá tu correo y contraseña</p>
            </header>

{{-- Errores --}}
@php
  $emailError = $errors->has('email') ? $errors->first('email') : null;
@endphp

@if($emailError && \Illuminate\Support\Str::contains($emailError, 'Cuenta suspendida'))
  {{-- Mensaje específico para cuenta suspendida (amarillo) --}}
  <div class="mb-4 p-3 bg-yellow-50 text-yellow-800 text-sm rounded-lg border border-yellow-100">
    {{ $emailError }}
  </div>
@endif

@if ($errors->any())
  {{-- Mostrar otros errores (rojo), excluyendo el mensaje de suspensión ya mostrado --}}
  @php
    $other = collect($errors->all())->reject(function($e) use ($emailError){
        return $e === $emailError;
    })->all();
  @endphp

  @if(!empty($other))
    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">
      <ul class="list-disc ml-4">
        @foreach ($other as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
@endif


            <form method="POST" action="{{ route('login') }}" class="space-y-6" autocomplete="on">
              @csrf

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    {{-- Mail (SVG correcto) --}}
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7a2 2 0 012-2h14a2 2 0 012 2v.217a2 2 0 01-.894 1.664l-7 4.667a2 2 0 01-2.212 0l-7-4.667A2 2 0 013 7.217V7z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/>
                    </svg>
                  </span>
                  <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                         class="txt focus-ring w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="nombre@empresa.com">
                </div>
              </div>

              {{-- Password --}}
              <div x-data="{show:false}">
                <div class="flex items-center justify-between mb-1.5">
                  <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                  @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-violet-700 hover:underline">
                      ¿Olvidó su contraseña?
                    </a>
                  @endif
                </div>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    {{-- Lock (SVG correcto) --}}
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 10-8 0v4m-2 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                  </span>
                  <input id="password" name="password" :type="show ? 'text' : 'password'" required
                         class="txt focus-ring w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="••••••••" autocomplete="current-password">
                  {{-- Toggle mostrar/ocultar (SVG correctos + x-cloak) --}}
                  <button type="button" @click="show=!show"
                          class="absolute inset-y-0 right-0 pr-3 flex items-center"
                          aria-label="Mostrar u ocultar contraseña">
                    <svg x-show="!show" class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3l18 18M10.477 10.477A3 3 0 0012 15c1.657 0 3-1.343 3-3a3 3 0 00-3-3c-.525 0-1.02.135-1.45.373M9.88 9.88L6.343 6.343M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m3.32-2.91A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.186 2.592"/>
                    </svg>
                  </button>
                </div>
              </div>

              {{-- Recordarme --}}
              <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 text-violet-700 border-slate-300 rounded focus:ring-violet-600">
                <label for="remember" class="ml-2 block text-sm text-slate-700">Recordar sesión</label>
              </div>

              {{-- Acción --}}
              <button type="submit"
                      class="btn w-full py-2.5 px-4 rounded-md text-white bg-slate-900 hover:bg-black focus:ring-2 focus:ring-violet-700 focus:ring-offset-2">
                Iniciar sesión
              </button>
            </form>

            {{-- Registro --}}
            <div class="mt-6 pt-6 border-t border-slate-200 text-center text-sm text-slate-500">
              ¿No tiene una cuenta?
              @if (Route::has('plans'))
                <a href="{{ route('plans') }}" class="font-medium text-violet-700 hover:underline">
                  Ver planes y registrarse
                </a>
              @endif
            </div>
          </div>
        </section>

      </div>

      <div class="text-center mt-6">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gestior by Leandro Cebreiros — Todos los derechos reservados.</p>
      </div>
    </div>
  </div>

  @livewireScripts
</body>
</html>

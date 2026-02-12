<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Registrarse • Gestior</title>

  {{-- Fuentes --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --bg-deep-1:#0b1020; --bg-deep-2:#0f172a; --stroke: rgba(168,85,247,.22);
      --violet:#7c3aed; --violet-500:#8b5cf6; --violet-400:#a78bfa;
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

        {{-- IZQUIERDA: Branding Gestior (logo grande, sin contenedor, sin nombre) --}}
        <section class="brand-pane p-8 md:p-12 flex items-center">
          <div class="relative z-10 w-full text-center md:text-left">
            {{-- Logo más grande, sin bg/contorno --}}
            <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-24 md:h-28 lg:h-32 w-auto select-none" />

            <h1 class="mt-6 text-4xl md:text-5xl font-extrabold tracking-tight">¡Hola, bienvenido!</h1>
            <p class="mt-2 text-slate-300/95 text-sm md:text-base">
              Toda la gestión que necesitas, en un solo lugar
            </p>

            {{-- Listado de capacidades de la herramienta --}}
            <ul class="mt-8 space-y-3 text-slate-200/95 text-sm md:text-base">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-violet-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Todo ordenado en un lugar
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-violet-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Creá pedidos y comprobantes
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-violet-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Controlá el stock en tiempo real
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-violet-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Administrá tus productos
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 text-violet-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Calculá y monitoreá tus gastos
              </li>
            </ul>
          </div>
        </section>

        {{-- DERECHA: Formulario (mismo estilo) --}}
        <section class="bg-white text-slate-900 p-8 md:p-12 split-stroke">
          <div class="max-w-md mx-auto">
            <header class="mb-6">
              <h2 class="text-2xl font-semibold tracking-tight">Crear cuenta</h2>
              <p class="text-sm text-slate-500 mt-1">Complete sus datos para registrarse</p>
            </header>

            {{-- Errores Jetstream/validación --}}
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('register') }}" class="space-y-6" autocomplete="on">
              @csrf

{{-- Key --}}
<div>
  <label for="invitation_key" class="block text-sm font-medium text-slate-700 mb-1.5">Clave de acceso</label>
  <input id="invitation_key" name="invitation_key" type="text" value="{{ old('invitation_key') }}" required
         class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
         placeholder="Ingrese su clave">
</div>

              {{-- Nombre --}}
              <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400">
              </div>

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7a2 2 0 012-2h14a2 2 0 012 2v.217a2 2 0 01-.894 1.664l-7 4.667a2 2 0 01-2.212 0l-7-4.667A2 2 0 013 7.217V7z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/>
                    </svg>
                  </span>
                  <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                 class="txt focus-ring w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                 placeholder="nombre@empresa.com">
                </div>
              </div>

              {{-- Tipo de negocio --}}
              <div>
                <label for="business_type" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de negocio</label>
                <select id="business_type" name="business_type"
                        class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white text-sm">
                  <option value="comercio" @selected(old('business_type', 'comercio') === 'comercio')>Comercio / Tienda</option>
                  <option value="alquiler" @selected(old('business_type') === 'alquiler')>Alquiler / Estacionamiento</option>
                </select>
                <p class="mt-1.5 text-xs text-slate-500">
                  Seleccione el tipo que mejor describe su negocio. Esto personalizará la interfaz para sus necesidades.
                </p>
              </div>

              {{-- Password --}}
              <div x-data="{show:false}">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 10-8 0v4m-2 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                  </span>
                  <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                         class="txt focus-ring w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="••••••••">
                  <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 pr-3 flex items-center" aria-label="Mostrar u ocultar contraseña">
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

              {{-- Confirm Password --}}
              <div x-data="{show2:false}">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 10-8 0v4m-2 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                  </span>
                  <input id="password_confirmation" name="password_confirmation" :type="show2 ? 'text' : 'password'" required autocomplete="new-password"
                         class="txt focus-ring w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="••••••••">
                  <button type="button" @click="show2=!show2" class="absolute inset-y-0 right-0 pr-3 flex items-center" aria-label="Mostrar u ocultar confirmación">
                    <svg x-show="!show2" class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                    </svg>
                    <svg x-show="show2" x-cloak class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3l18 18M10.477 10.477A3 3 0 0012 15c1.657 0 3-1.343 3-3a3 3 0 00-3-3c-.525 0-1.02.135-1.45.373M9.88 9.88L6.343 6.343M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m3.32-2.91A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.186 2.592"/>
                    </svg>
                  </button>
                </div>
              </div>

              {{-- Términos (si Jetstream lo habilita) --}}
              @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="flex items-start gap-2">
                  <input id="terms" name="terms" type="checkbox" required
                         class="h-4 w-4 text-violet-700 border-slate-300 rounded focus:ring-violet-600">
                  <label for="terms" class="text-sm text-slate-600">
                    {!! __('Acepto los :terms_of_service y la :privacy_policy', [
                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-violet-700 hover:text-violet-900">'.__('Condiciones de servicio').'</a>',
                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-violet-700 hover:text-violet-900">'.__('Política de privacidad').'</a>',
                    ]) !!}
                  </label>
                </div>
              @endif

              {{-- Acción --}}
              <button type="submit"
                      class="btn w-full py-2.5 px-4 rounded-md text-white bg-slate-900 hover:bg-black focus:ring-2 focus:ring-violet-700 focus:ring-offset-2">
                Registrarse
              </button>

              <div class="text-center text-sm text-slate-500">
                ¿Ya tiene cuenta?
                <a href="{{ route('login') }}" class="font-medium text-violet-700 hover:underline">Inicie sesión</a>
              </div>
            </form>
          </div>
        </section>

      </div>

      <div class="text-center mt-6">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gestior — Todos los derechos reservados.</p>
      </div>
    </div>
  </div>

  @livewireScripts
</body>
</html>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Registro {{ $planName }} • Gestior</title>

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

    /* Fondo abstracto degradé */
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
    <div class="w-full max-w-lg">

      {{-- Logo --}}
      <div class="text-center mb-8">
        <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-16 w-auto mx-auto select-none" />
      </div>

      <div class="card bg-white p-8 md:p-10">
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Solicitar acceso</h2>
            <span class="px-3 py-1 rounded-full text-xs font-semibold
                       {{ $plan === 'basic' ? 'bg-slate-100 text-slate-700' : '' }}
                       {{ $plan === 'premium' ? 'bg-violet-100 text-violet-700' : '' }}
                       {{ $plan === 'enterprise' ? 'bg-blue-100 text-blue-700' : '' }}">
              Plan {{ $planName }}
            </span>
          </div>
          <p class="text-sm text-slate-500">Complete sus datos para solicitar acceso gratuito</p>
        </div>

        {{-- Errores --}}
        @if ($errors->any())
          <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200">
            <ul class="text-sm text-red-600 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
          @csrf
          <input type="hidden" name="plan" value="{{ $plan }}">

          {{-- Nombre --}}
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                   placeholder="Juan Pérez">
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 7a2 2 0 012-2h14a2 2 0 012 2v.217a2 2 0 01-.894 1.664l-7 4.667a2 2 0 01-2.212 0l-7-4.667A2 2 0 013 7.217V7z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/>
                </svg>
              </span>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required
                     class="txt focus-ring w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                     placeholder="juan@empresa.com">
            </div>
          </div>

          {{-- Tipo de negocio --}}
          <div>
            <label for="business_type" class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de negocio</label>
            <select id="business_type" name="business_type" required
                    class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white text-sm">
              <option value="comercio" @selected(old('business_type', 'comercio') === 'comercio')>Comercio / Tienda</option>
              <option value="alquiler" @selected(old('business_type') === 'alquiler')>Alquiler / Estacionamiento</option>
            </select>
            <p class="mt-1.5 text-xs text-slate-500">
              Seleccione el tipo que mejor describe su negocio. Esto personalizará su experiencia.
            </p>
          </div>

          {{-- Info --}}
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">¿Qué sucede después?</p>
                <ul class="space-y-1 text-blue-700">
                  <li>• Revisaremos tu solicitud</li>
                  <li>• Te enviaremos las credenciales de acceso por email</li>
                  <li>• Podrás comenzar a usar Gestior inmediatamente</li>
                </ul>
              </div>
            </div>
          </div>

          {{-- Submit --}}
          <button type="submit"
                  class="btn w-full py-3 px-4 rounded-md text-white font-semibold
                       {{ $plan === 'premium'
                          ? 'bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-600 hover:to-purple-600'
                          : 'bg-slate-900 hover:bg-black' }}
                       focus:ring-2 focus:ring-violet-700 focus:ring-offset-2 shadow-lg">
            Solicitar acceso gratis
          </button>

          <div class="text-center text-sm text-slate-500">
            <a href="{{ route('plans') }}" class="font-medium text-violet-700 hover:underline">
              ← Volver a planes
            </a>
            <span class="mx-2">|</span>
            <a href="{{ route('login') }}" class="font-medium text-violet-700 hover:underline">
              ¿Ya tienes cuenta? Inicia sesión
            </a>
          </div>
        </form>
      </div>

      <div class="text-center mt-6">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gestior — Todos los derechos reservados.</p>
      </div>
    </div>
  </div>

  @livewireScripts
</body>
</html>

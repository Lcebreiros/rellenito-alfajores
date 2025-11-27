<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Planes • Gestior</title>

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

    .plan-card{
      transition: all 0.3s ease;
      border-radius: 1.5rem; overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,.35);
      background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
      backdrop-filter: blur(2px);
      border: 1px solid rgba(255,255,255,.06);
    }

    .plan-card:hover{
      transform: translateY(-8px);
      box-shadow: 0 30px 80px rgba(0,0,0,.45);
      border-color: rgba(139,92,246,.3);
    }

    .plan-card.recommended{
      border-color: rgba(139,92,246,.5);
      box-shadow: 0 0 0 2px rgba(139,92,246,.2), 0 20px 60px rgba(0,0,0,.35);
    }
  </style>

  @livewireStyles
</head>
<body class="h-full">
  <div class="abstract-bg"></div>

  <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    {{-- Logo --}}
    <div class="mb-8">
      <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-16 w-auto select-none" />
    </div>

    {{-- Header --}}
    <div class="text-center mb-12 max-w-3xl">
      <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
        Elige el plan perfecto para ti
      </h1>
      <p class="text-lg text-slate-300">
        Prueba gratis cualquier plan. Actívalo en minutos.
      </p>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 w-full max-w-6xl mb-8">

      {{-- Plan Básico --}}
      <div class="plan-card relative">
        <div class="p-8">
          <h3 class="text-2xl font-bold text-white mb-2">Básico</h3>
          <p class="text-slate-400 text-sm mb-6">Ideal para emprendedores</p>

          <div class="mb-6">
            <div class="text-4xl font-extrabold text-white">Gratis</div>
            <div class="text-sm text-slate-400">durante el periodo de prueba</div>
          </div>

          <ul class="space-y-3 mb-8 text-slate-300">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Gestión de pedidos básica</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Control de stock</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Hasta 100 productos</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">1 usuario</span>
            </li>
          </ul>

          <a href="{{ route('register.with-plan', 'basic') }}"
             class="block w-full py-3 px-4 rounded-xl text-center font-semibold
                    bg-white/10 hover:bg-white/15 text-white border border-white/20
                    transition-all duration-200">
            Empezar gratis
          </a>
        </div>
      </div>

      {{-- Plan Premium --}}
      <div class="plan-card recommended relative">
        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-violet-500 to-purple-500"></div>
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-violet-500 to-purple-500 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">
          Recomendado
        </div>

        <div class="p-8">
          <h3 class="text-2xl font-bold text-white mb-2">Premium</h3>
          <p class="text-slate-400 text-sm mb-6">Para negocios en crecimiento</p>

          <div class="mb-6">
            <div class="text-4xl font-extrabold text-white">Gratis</div>
            <div class="text-sm text-slate-400">durante el periodo de prueba</div>
          </div>

          <ul class="space-y-3 mb-8 text-slate-300">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm font-semibold text-white">Todo de Básico, más:</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Productos ilimitados</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Hasta 5 usuarios</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Dashboard avanzado</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Soporte prioritario</span>
            </li>
          </ul>

          <a href="{{ route('register.with-plan', 'premium') }}"
             class="block w-full py-3 px-4 rounded-xl text-center font-semibold
                    bg-gradient-to-r from-violet-500 to-purple-500 hover:from-violet-600 hover:to-purple-600
                    text-white shadow-lg shadow-violet-500/30
                    transition-all duration-200">
            Empezar gratis
          </a>
        </div>
      </div>

      {{-- Plan Enterprise --}}
      <div class="plan-card relative">
        <div class="p-8">
          <h3 class="text-2xl font-bold text-white mb-2">Enterprise</h3>
          <p class="text-slate-400 text-sm mb-6">Para empresas establecidas</p>

          <div class="mb-6">
            <div class="text-4xl font-extrabold text-white">Gratis</div>
            <div class="text-sm text-slate-400">durante el periodo de prueba</div>
          </div>

          <ul class="space-y-3 mb-8 text-slate-300">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm font-semibold text-white">Todo de Premium, más:</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Usuarios ilimitados</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Múltiples sucursales</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">API personalizada</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">Soporte 24/7</span>
            </li>
          </ul>

          <a href="{{ route('register.with-plan', 'enterprise') }}"
             class="block w-full py-3 px-4 rounded-xl text-center font-semibold
                    bg-white/10 hover:bg-white/15 text-white border border-white/20
                    transition-all duration-200">
            Empezar gratis
          </a>
        </div>
      </div>

    </div>

    {{-- Footer --}}
    <div class="text-center space-y-3">
      <p class="text-slate-400 text-sm">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="text-violet-400 hover:text-violet-300 font-medium">Inicia sesión</a>
      </p>
      <p class="text-xs text-slate-500">&copy; {{ date('Y') }} Gestior — Todos los derechos reservados.</p>
    </div>
  </div>

  @livewireScripts
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Iniciar sesión</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600&display=swap" rel="stylesheet" />

  <!-- 👇 CORREGIDO: Usar Vite en lugar de CDN -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  <style>
    body { font-family: 'Inter', sans-serif; letter-spacing: -0.015rem; }
    .input-focus-effect:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
    .btn-transition { transition: all .2s ease; }
    .btn-transition:hover { transform: translateY(-1px); }
    [x-cloak]{display:none!important}
  </style>

  <!-- 👇 AGREGADO: Livewire styles -->
  @livewireStyles
</head>
<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

      <div class="text-center mb-10">
        <div class="flex justify-center mb-5">
          <div class="w-14 h-14 bg-neutral-900 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
            </svg>
          </div>
        </div>
        <h1 class="text-2xl font-light text-neutral-900 tracking-tight">Acceder al sistema</h1>
        <p class="mt-2 text-sm text-neutral-500">Ingrese sus credenciales para continuar</p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-neutral-100 p-8">

        {{-- Mensajes de validación --}}
        @if ($errors->any())
          <div class="mb-6 p-3 bg-red-50 text-red-700 text-sm rounded-md border border-red-100">
            <ul class="list-disc ml-4">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- LOGIN --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-6" autocomplete="on">
          @csrf

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">Correo electrónico</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                     class="w-full pl-10 pr-3 py-2.5 border border-neutral-200 rounded-md focus:outline-none input-focus-effect focus:border-blue-500 text-neutral-700 placeholder-neutral-400"
                     placeholder="nombre@empresa.com" />
            </div>
          </div>

          {{-- Password --}}
          <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="block text-sm font-medium text-neutral-700">Contraseña</label>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm font-medium text-blue-600 hover:text-blue-500">¿Olvidó su contraseña?</a>
              @endif
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <input id="password" name="password" :type="show ? 'text' : 'password'" required
                     class="w-full pl-10 pr-10 py-2.5 border border-neutral-200 rounded-md focus:outline-none input-focus-effect focus:border-blue-500 text-neutral-700 placeholder-neutral-400"
                     placeholder="••••••••" />
              <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg x-show="!show" class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="show" x-cloak class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L17 17m-7.122-7.122L3 3"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Remember me --}}
          <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                   class="h-4 w-4 text-blue-600 border-neutral-300 rounded focus:ring-blue-500" />
            <label for="remember" class="ml-2 block text-sm text-neutral-700">Recordar sesión</label>
          </div>

          {{-- Submit --}}
          <div>
            <button type="submit"
              class="w-full flex justify-center py-2.5 px-4 rounded-md shadow-sm btn-transition text-sm font-medium text-white bg-neutral-800 hover:bg-neutral-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Iniciar sesión
            </button>
          </div>
        </form>

        <div class="mt-6 pt-6 border-t border-neutral-100 text-center text-sm text-neutral-500">
          ¿No tiene una cuenta?
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">Regístrese aquí</a>
          @endif
        </div>
      </div>

      <div class="text-center mt-8">
        <p class="text-xs text-neutral-400">&copy; {{ date('Y') }} Sistema. Todos los derechos reservados.</p>
      </div>
    </div>
  </div>

  <!-- 👇 AGREGADO: Livewire scripts -->
  @livewireScripts
</body>
</html>
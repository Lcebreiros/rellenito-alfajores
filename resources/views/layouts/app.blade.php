<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ \App\Models\Setting::get('theme', 'light') === 'dark' ? 'dark' : '' }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  {{-- Fuentes --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Estado inicial del sidebar antes de Alpine --}}
  <script>
    (function () {
      const collapsed = localStorage.getItem('sidebar:collapsed') === '1';
      document.documentElement.classList.toggle('sb-collapsed', collapsed);
    })();
  </script>

  <style>
    .app-main{
      margin-left: 18rem; /* w-72 */
      transition: margin-left .5s cubic-bezier(.16,1,.3,1);
      min-width: 0;
    }
    .sb-collapsed .app-main{ margin-left: 5rem; } /* w-20 */
    @media (max-width: 767px) { .app-main{ margin-left: 0; } }
  </style>
  @if (trim($__env->yieldContent('no_sidebar')))
  <style>
    .app-main { margin-left: 0 !important; }
  </style>
  @endif

  @livewireStyles
  @stack('styles')
</head>

<body class="font-sans antialiased dark:text-neutral-100"
      x-data
      x-init="
        window.addEventListener('sidebar:toggle', e => {
          document.documentElement.classList.toggle('sb-collapsed', e.detail === true);
        });
      ">

  <x-banner />

  {{-- Banner de verificación de email (se extiende por todo el ancho) --}}
  @if (auth()->check() && !auth()->user()->hasVerifiedEmail())
    <div class="fixed top-0 left-0 right-0 z-50 bg-yellow-50 dark:bg-yellow-900/20 border-b-4 border-yellow-400 dark:border-yellow-600 p-4">
        <div class="flex items-center justify-center max-w-7xl mx-auto">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <span class="font-medium">Tu correo electrónico no está verificado.</span>
                    Por favor verifica tu email para asegurar tu cuenta.
                </p>
            </div>
            <div class="ml-3 flex-shrink-0">
                <a href="https://gestior.com.ar/email/verify"
                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                    Verificar ahora
                </a>
            </div>
        </div>
    </div>
    <style>
      /* Agregar padding-top al body cuando hay banner de verificación */
      body { padding-top: 72px; }
    </style>
  @endif

  {{-- Sidebar fijo (solo desktop) --}}
  @if (trim($__env->yieldContent('no_sidebar')))
    {{-- sin sidebar en esta vista --}}
  @else
    <x-sidebar />
  @endif

  {{-- Contenido principal --}}
  <div class="app-main min-h-screen flex flex-col {{ module_bg() }}">
    <x-mobile-header />

    {{-- HEADER: slot Jetstream o sección Blade --}}
    @if (isset($header))
      <header class="bg-white/80 backdrop-blur-md border-b border-white/60 dark:bg-neutral-900/60 dark:border-neutral-700/40 @if(request()->routeIs('inicio')) hidden md:block @endif">
        <div class="w-full py-4 px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">{{ $header }}</div>
            <div class="flex items-center gap-2" @if(!request()->routeIs('inicio')) x-data @endif>
              @hasSection('header_actions')
                @yield('header_actions')
              @endif
              @if (request()->routeIs('inicio'))
              <div x-data="{ open:false }" class="relative">
                <button type="button" @click="open=true"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" />
                  </svg>
                  Salir
                </button>
                <!-- Modal de confirmación logout -->
                <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center">
                  <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                  <div class="relative w-full max-w-md mx-4 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-lg p-5">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Confirmar salida</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-4">¿Está seguro que desea cerrar sesión?</p>
                    <div class="flex items-center justify-end gap-2">
                      <button type="button" @click="open=false" class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancelar</button>
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white text-sm hover:bg-rose-700">Cerrar sesión</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              <x-server-status />
              <x-notifications-bell />
            </div>
          </div>
        </div>
      </header>
    @else
      @hasSection('header')
        <header class="bg-white/80 backdrop-blur-md border-b border-white/60 dark:bg-neutral-900/60 dark:border-neutral-700/40 @if(request()->routeIs('inicio')) hidden md:block @endif">
          <div class="w-full py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
              <div class="min-w-0">@yield('header')</div>
              <div class="flex items-center gap-2">
                @hasSection('header_actions')
                  @yield('header_actions')
                @endif
                @if (request()->routeIs('inicio'))
                <div x-data="{ open:false }" class="relative">
                  <button type="button" @click="open=true"
                          class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" />
                    </svg>
                    Salir
                  </button>
                  <!-- Modal de confirmación logout -->
                  <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                    <div class="relative w-full max-w-md mx-4 rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-lg p-5">
                      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Confirmar salida</h3>
                      <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-4">¿Está seguro que desea cerrar sesión?</p>
                      <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="open=false" class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancelar</button>
                        <form method="POST" action="{{ route('logout') }}">
                          @csrf
                          <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white text-sm hover:bg-rose-700">Cerrar sesión</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                <x-server-status />
                <x-notifications-bell />
              </div>
            </div>
          </div>
        </header>
      @endif
    @endif

    {{-- CONTENIDO: padding extra en mobile para no tapar con la bottom bar --}}
    <main class="flex-1 p-4 md:p-6 pb-6 md:pb-6">
      @if (isset($slot))
        {{ $slot }}
      @else
        @yield('content')
      @endif
    </main>
  </div>

  {{-- Modal de bienvenida --}}
  <livewire:welcome-modal />

  {{-- ⛔ Drawer mobile reemplazado por la bottom bar --}}
  {{-- <x-mobile-drawer /> --}}

  @stack('modals')
  @push('modals')
  <livewire:order-quick-modal />
@endpush

  @livewireScripts

  {{-- Manejador de sesión expirada (error 419) --}}
  <script>
    // Manejar errores 419 en peticiones Livewire
    document.addEventListener('livewire:init', () => {
      Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
          if (status === 419) {
            preventDefault();

            // Mostrar mensaje amigable
            if (confirm('Su sesión ha expirado. ¿Desea recargar la página para iniciar sesión nuevamente?')) {
              window.location.href = '{{ route('login') }}';
            }
          }
        });
      });
    });

    // Manejar errores 419 en peticiones AJAX/Fetch globales
    window.addEventListener('unhandledrejection', (event) => {
      if (event.reason?.status === 419 || event.reason?.response?.status === 419) {
        event.preventDefault();

        if (confirm('Su sesión ha expirado. ¿Desea iniciar sesión nuevamente?')) {
          window.location.href = '{{ route('login') }}';
        }
      }
    });
  </script>

  {{-- Tema instantáneo si Livewire emite `theme-updated` --}}
  <script>
    window.addEventListener('theme-updated', (e) => {
      const theme = e.detail?.theme || 'light';
      document.documentElement.classList.toggle('dark', theme === 'dark');
    });
  </script>

  {{-- Global search (Ctrl/Cmd + K) --}}
  <x-global-search />

  {{-- Toast notifications container --}}
  <x-toast-container />

  {{-- Menú flotante móvil (reemplaza la bottom bar) --}}
  @if (!request()->routeIs(['inicio','login','register']))
    <x-mobile-fab-menu />
  @endif

  @stack('scripts')
</body>
</html>

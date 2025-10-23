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
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-neutral-950 dark:text-neutral-100"
      x-data
      x-init="
        window.addEventListener('sidebar:toggle', e => {
          document.documentElement.classList.toggle('sb-collapsed', e.detail === true);
        });
      ">

  <x-banner />

  {{-- Sidebar fijo (solo desktop) --}}
  @if (trim($__env->yieldContent('no_sidebar')))
    {{-- sin sidebar en esta vista --}}
  @else
    <x-sidebar />
  @endif

  {{-- Contenido principal --}}
  <div class="app-main min-h-screen flex flex-col">
    <x-mobile-header />

    {{-- HEADER: slot Jetstream o sección Blade --}}
    @if (isset($header))
      <header class="bg-white border-b border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800">
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
              <x-notifications-bell />
            </div>
          </div>
        </div>
      </header>
    @else
      @hasSection('header')
        <header class="bg-white border-b border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800">
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
                <x-notifications-bell />
              </div>
            </div>
          </div>
        </header>
      @endif
    @endif

    {{-- CONTENIDO: padding extra en mobile para no tapar con la bottom bar --}}
    <main class="flex-1 p-4 md:p-6 pb-24 md:pb-6">
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

  {{-- Tema instantáneo si Livewire emite `theme-updated` --}}
  <script>
    window.addEventListener('theme-updated', (e) => {
      const theme = e.detail?.theme || 'light';
      document.documentElement.classList.toggle('dark', theme === 'dark');
    });
  </script>

  {{-- ===== Bottom bar (usa tus imágenes y añade el menú "Más") ===== --}}
  {{-- Espaciador para que nada quede detrás de la barra en mobile --}}
  <div class="h-16 md:hidden"></div>
  <x-bottom-nav />

  @stack('scripts')
</body>
</html>

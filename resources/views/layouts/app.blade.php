@php
    $theme = \App\Models\Setting::get('theme', 'light');
    $themeClass = '';
    if ($theme === 'dark') {
        $themeClass = 'dark';
    } elseif ($theme === 'neon') {
        // Neon hereda dark + agrega efectos neón
        $themeClass = 'dark theme-neon';
    } elseif ($theme === 'custom') {
        // Tema personalizado con o sin modo oscuro
        $darkValue = \App\Models\Setting::get('custom_theme_dark', 'false');
        $customThemeDark = filter_var($darkValue, FILTER_VALIDATE_BOOLEAN);
        $themeClass = $customThemeDark ? 'dark theme-custom' : 'theme-custom';
    } elseif ($theme !== 'light') {
        $themeClass = 'theme-' . $theme;
    }
    $customColor = \App\Models\Setting::get('custom_color', '#6366f1');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ $themeClass }}"
      data-theme="{{ $theme }}"
      data-custom-color="{{ $customColor }}">

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
    // Definir función global para aplicar color personalizado PRIMERO
    window.applyCustomColor = function(hexColor) {
      if (!hexColor) return;
      // Convertir hex a RGB
      const hex = hexColor.replace('#', '');
      const r = parseInt(hex.substring(0, 2), 16);
      const g = parseInt(hex.substring(2, 4), 16);
      const b = parseInt(hex.substring(4, 6), 16);
      // Aplicar el color como CSS variable
      document.documentElement.style.setProperty('--custom-color-rgb', `${r} ${g} ${b}`);
    };

    (function () {
      // Leer estado guardado del sidebar (por defecto expandido)
      const collapsed = localStorage.getItem('sidebar:collapsed') === '1';
      document.documentElement.classList.toggle('sb-collapsed', collapsed);

      const customDarkFromServer = {{ $theme === 'custom' ? ($customThemeDark ? 'true' : 'false') : 'false' }};

      function applyThemeClasses(themeOverride) {
        const html = document.documentElement;
        const storedTheme = localStorage.getItem('theme');
        const theme = themeOverride || storedTheme || html.dataset.theme || 'light';
        html.classList.remove('dark', 'theme-neon', 'theme-custom');

        if (theme === 'dark') {
          html.classList.add('dark');
        } else if (theme === 'neon') {
          html.classList.add('dark', 'theme-neon');
        } else if (theme === 'custom') {
          const customDark = customDarkFromServer || html.dataset.customThemeDark === 'true';
          if (customDark) {
            html.classList.add('dark', 'theme-custom');
          } else {
            html.classList.add('theme-custom');
          }
          if (window.applyCustomColor) {
            const cc = html.dataset.customColor || '{{ $customColor }}';
            window.applyCustomColor(cc);
          }
        } else if (theme && theme !== 'light') {
          html.classList.add('theme-' + theme);
        }
        html.setAttribute('data-theme', theme);
      }

      applyThemeClasses();
      document.addEventListener('livewire:navigated', () => applyThemeClasses(), { once: false });
      document.addEventListener('turbo:load', () => applyThemeClasses(), { once: false });
    })();
  </script>

  <style>
    /* Prevenir scroll horizontal global */
    html, body {
      overflow-x: hidden;
      max-width: 100vw;
    }

    :root {
      --sb-width: 16rem;
      --sb-width-collapsed: 4rem;
    }
    .app-main{
      margin-left: var(--sb-width);
      width: calc(100vw - var(--sb-width));
      transition: margin-left .5s cubic-bezier(.16,1,.3,1), width .5s cubic-bezier(.16,1,.3,1);
      min-width: 0;
      overflow-x: hidden;
    }
    .sb-collapsed .app-main{
      margin-left: var(--sb-width-collapsed);
      width: calc(100vw - var(--sb-width-collapsed));
    }
    @media (max-width: 1024px) {
      :root {
        --sb-width: 0;
        --sb-width-collapsed: 0;
      }
      .app-main{
        margin-left: 0;
        width: 100vw;
      }
    }
  </style>
  @if (trim($__env->yieldContent('no_sidebar')))
  <style>
    .app-main {
      margin-left: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
    }
  </style>
  @endif
  @if($theme === 'neon')
  <style id="neon-inline-vars">
    :root.theme-neon, html.theme-neon {
      --theme-bg-from: 10 10 11;
      --theme-bg-via: 23 23 23;
      --theme-bg-to: 15 15 18;
      --module-orders-50: 45 5 25;
      --module-orders-100: 60 10 35;
      --module-orders-200: 110 15 70;
      --module-orders-300: 165 25 105;
      --module-orders-400: 210 35 135;
      --module-orders-500: 255 0 128;
      --module-orders-600: 255 20 147;
      --module-orders-700: 255 40 167;
      --module-products-50: 0 25 35;
      --module-products-100: 0 35 45;
      --module-products-200: 0 75 95;
      --module-products-300: 0 115 140;
      --module-products-400: 0 175 200;
      --module-products-500: 0 255 255;
      --module-products-600: 20 255 255;
      --module-products-700: 40 255 255;
      --module-clients-50: 0 35 15;
      --module-clients-100: 0 45 20;
      --module-clients-200: 10 80 35;
      --module-clients-300: 20 120 55;
      --module-clients-400: 30 180 75;
      --module-clients-500: 57 255 20;
      --module-clients-600: 77 255 40;
      --module-clients-700: 97 255 60;
      --module-dashboard-50: 25 0 45;
      --module-dashboard-100: 35 0 60;
      --module-dashboard-200: 80 0 120;
      --module-dashboard-300: 120 0 170;
      --module-dashboard-400: 160 0 210;
      --module-dashboard-500: 191 0 255;
      --module-dashboard-600: 201 20 255;
      --module-dashboard-700: 211 40 255;
      --module-expenses-50: 40 15 0;
      --module-expenses-100: 50 20 0;
      --module-expenses-200: 100 45 5;
      --module-expenses-300: 150 70 8;
      --module-expenses-400: 200 100 10;
      --module-expenses-500: 255 128 0;
      --module-expenses-600: 255 148 20;
      --module-expenses-700: 255 168 40;
      --module-company-50: 15 15 25;
      --module-company-100: 20 20 30;
      --module-company-200: 90 90 140;
      --module-company-300: 120 120 175;
      --module-company-400: 150 150 215;
      --module-company-500: 180 180 255;
      --module-company-600: 190 190 255;
      --module-company-700: 200 200 255;
      --module-employees-50: 0 30 30;
      --module-employees-100: 0 40 40;
      --module-employees-200: 0 90 85;
      --module-employees-300: 0 140 130;
      --module-employees-400: 0 200 175;
      --module-employees-500: 0 255 200;
      --module-employees-600: 20 255 210;
      --module-employees-700: 40 255 220;
      --module-services-50: 35 0 20;
      --module-services-100: 45 0 30;
      --module-services-200: 90 0 60;
      --module-services-300: 140 0 95;
      --module-services-400: 190 0 125;
      --module-services-500: 255 20 147;
      --module-services-600: 255 40 167;
      --module-services-700: 255 60 187;
      --module-stock-50: 0 30 40;
      --module-stock-100: 0 40 50;
      --module-stock-200: 0 85 105;
      --module-stock-300: 0 135 150;
      --module-stock-400: 0 190 210;
      --module-stock-500: 0 242 255;
      --module-stock-600: 20 245 255;
      --module-stock-700: 40 248 255;
      --module-payment-50: 40 35 0;
      --module-payment-100: 50 45 0;
      --module-payment-200: 110 100 5;
      --module-payment-300: 160 145 8;
      --module-payment-400: 210 190 10;
      --module-payment-500: 255 255 0;
      --module-payment-600: 255 255 51;
      --module-payment-700: 255 255 102;
    }
  </style>
  @endif

  @livewireStyles
  @stack('styles')
</head>

<body class="font-sans antialiased dark:text-neutral-100 overflow-x-hidden">

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
  <div class="app-main min-h-screen flex flex-col {{ module_bg() }} overflow-x-hidden w-full">
    <x-mobile-header />

    {{-- HEADER: slot Jetstream o sección Blade --}}
    @if (isset($header))
      <header class="header-glass @if(request()->routeIs('inicio')) hidden md:block @endif">
        <div class="w-full py-3 sm:py-4 px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
            <div class="flex-1 min-w-0">{{ $header }}</div>
            @php
              $hasActions = trim($__env->yieldContent('header_actions')) !== '' || request()->routeIs('inicio');
            @endphp
            @if($hasActions)
              <div class="hidden sm:block h-8 w-px bg-neutral-200 dark:bg-neutral-700/70 flex-shrink-0"></div>
              <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                @hasSection('header_actions')
                  @yield('header_actions')
                @endif
                @if (request()->routeIs('inicio'))
                  <div x-data="{ open:false }" class="relative flex-shrink-0">
                    <button type="button" @click="open=true"
                            class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800/80 hover:border-neutral-400 dark:hover:border-neutral-600 transition-all">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" />
                      </svg>
                      <span class="hidden lg:inline">Cerrar sesión</span>
                      <span class="lg:hidden">Salir</span>
                    </button>
                    <!-- Modal de confirmación logout -->
                    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open=false"></div>
                      <div class="relative w-full max-w-md rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-2xl p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Confirmar salida</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-5">¿Está seguro que desea cerrar sesión?</p>
                        <div class="flex items-center justify-end gap-2">
                          <button type="button" @click="open=false" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancelar</button>
                          <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white text-sm font-medium hover:bg-rose-700 transition-colors shadow-sm">Cerrar sesión</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            @endif
          </div>
        </div>
      </header>
    @else
      @hasSection('header')
        <header class="header-glass @if(request()->routeIs('inicio')) hidden md:block @endif">
          <div class="w-full py-3 sm:py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
              <div class="flex-1 min-w-0">@yield('header')</div>
              @php
                $hasActions = trim($__env->yieldContent('header_actions')) !== '' || request()->routeIs('inicio');
              @endphp
              @if($hasActions)
                <div class="hidden sm:block h-8 w-px bg-neutral-200 dark:bg-neutral-700/70 flex-shrink-0"></div>
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                  @hasSection('header_actions')
                    @yield('header_actions')
                  @endif
                  @if (request()->routeIs('inicio'))
                    <div x-data="{ open:false }" class="relative flex-shrink-0">
                      <button type="button" @click="open=true"
                              class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800/80 hover:border-neutral-400 dark:hover:border-neutral-600 transition-all">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" />
                        </svg>
                        <span class="hidden lg:inline">Cerrar sesión</span>
                        <span class="lg:hidden">Salir</span>
                      </button>
                      <!-- Modal de confirmación logout -->
                      <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open=false"></div>
                        <div class="relative w-full max-w-md rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-2xl p-6">
                          <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Confirmar salida</h3>
                          <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-5">¿Está seguro que desea cerrar sesión?</p>
                          <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="open=false" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Cancelar</button>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                              @csrf
                              <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white text-sm font-medium hover:bg-rose-700 transition-colors shadow-sm">Cerrar sesión</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              @endif
            </div>
          </div>
        </header>
      @endif
    @endif

    {{-- CONTENIDO: padding extra en mobile para no tapar con la bottom bar --}}
    <main class="flex-1 p-4 md:p-6 pb-6 md:pb-6 w-full overflow-x-hidden">
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

      // Remover todas las clases de tema
      document.documentElement.classList.remove(
        'dark', 'theme-neon', 'theme-custom'
      );

      // Agregar la clase del nuevo tema
      if (theme === 'dark') {
        document.documentElement.classList.add('dark');
      } else if (theme === 'neon') {
        // Neon hereda dark + agrega efectos neón
        document.documentElement.classList.add('dark', 'theme-neon');
      } else if (theme !== 'light') {
        document.documentElement.classList.add('theme-' + theme);
      }
    });

    // Escuchar cambios en el color personalizado
    window.addEventListener('custom-color-updated', (e) => {
      const color = e.detail?.color || '#6366f1';
      window.applyCustomColor(color);

      // Asegurarse de que el tema custom esté activo
      if (!document.documentElement.classList.contains('theme-custom')) {
        document.documentElement.classList.remove('dark', 'theme-neon');
        document.documentElement.classList.add('theme-custom');
      }
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

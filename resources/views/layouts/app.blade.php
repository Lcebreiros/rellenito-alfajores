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
  <x-sidebar />

  {{-- Contenido principal --}}
  <div class="app-main min-h-screen flex flex-col">
    <x-mobile-header />

    {{-- HEADER: slot Jetstream o sección Blade --}}
    @if (isset($header))
      <header class="bg-white border-b border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          {{ $header }}
        </div>
      </header>
    @else
      @hasSection('header')
        <header class="bg-white border-b border-neutral-200 dark:bg-neutral-900 dark:border-neutral-800">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
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

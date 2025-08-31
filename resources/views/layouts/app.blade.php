<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{ csrf_token() }}">

      <title>{{ config('app.name', 'Laravel') }}</title>

      <!-- Fonts -->
      <link rel="preconnect" href="https://fonts.bunny.net">
      <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

      <!-- Vite -->
      @vite(['resources/css/app.css', 'resources/js/app.js'])

      <!-- APLICAR MARGEN INICIAL ANTES DE ALPINE (evita solape en primer paint) -->
      <script>
        (function () {
          const collapsed = localStorage.getItem('sidebar:collapsed') === '1';
          document.documentElement.classList.toggle('sb-collapsed', collapsed);
        })();
      </script>
      <style>
        /* margen inicial del contenido (sin depender de Alpine) */
        .app-main {
          margin-left: 18rem; /* = w-72 */
          transition: margin-left .5s cubic-bezier(.16,1,.3,1);
        }
        .sb-collapsed .app-main {
          margin-left: 5rem; /* = w-20 */
        }
        /* opcional en mobile: ocupar todo el ancho */
        @media (max-width: 767px) {
          .app-main { margin-left: 0; }
        }
      </style>

      @livewireStyles
  </head>
  <body class="font-sans antialiased bg-gray-100"
        x-data
        x-init="
          // Escucha el toggle del sidebar para actualizar el margen en caliente
          window.addEventListener('sidebar:toggle', e => {
            document.documentElement.classList.toggle('sb-collapsed', e.detail === true);
          });
        ">
      <x-banner />

      {{-- Sidebar fijo --}}
      <x-sidebar />

      {{-- Contenido principal --}}
      <div class="app-main min-h-screen flex flex-col min-w-0">
          {{-- Header mobile (opcional) --}}
          <x-mobile-header />

          {{-- Header de página --}}
          @hasSection('header')
              <header class="bg-white shadow">
                  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                      @yield('header')
                  </div>
              </header>
          @endif

          {{-- Contenido --}}
          <main class="flex-1 p-4 md:p-6">
              @yield('content')
          </main>
      </div>

      {{-- Drawer mobile (si lo usás) --}}
      <x-mobile-drawer />

      @stack('modals')
      @livewireScripts
      @stack('scripts')
  </body>
</html>

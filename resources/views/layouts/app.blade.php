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

      <!-- Scripts -->
      @vite(['resources/css/app.css', 'resources/js/app.js'])

      <!-- Styles -->
      @livewireStyles
  </head>
  <body class="font-sans antialiased bg-gray-100">
      <x-banner />

      {{-- Layout principal --}}
      <div class="min-h-screen flex">
          {{-- Sidebar para desktop --}}
          <x-sidebar />

          {{-- Contenido principal --}}
          <div class="flex-1 flex flex-col min-w-0">
              {{-- Header mobile (opcional si lo tenés) --}}
              <x-mobile-header />

              {{-- Header de página (sección opcional) --}}
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
      </div>

      {{-- Drawer mobile (si lo usás) --}}
      <x-mobile-drawer />

      @stack('modals')
      @livewireScripts
  </body>
</html>

<!-- resources/views/layouts/guest.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <!-- ... -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- QUITAR estas dos líneas -->
    {{-- @livewireStyles --}}
  </head>
  <body>
    <div class="font-sans text-gray-900 antialiased">
      {{ $slot }}
    </div>
    <!-- QUITAR esta línea -->
    {{-- @livewireScripts --}}
  </body>
</html>

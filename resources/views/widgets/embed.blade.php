{{-- resources/views/widgets/embed.blade.php --}}
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="color-scheme" content="light dark">
  @vite(['resources/css/app.css','resources/js/app.js']) {{-- donde cargas Alpine/Livewire --}}
  @livewireStyles
  <style>html,body{background:transparent;height:100%;}</style>
</head>
<body class="h-full">
  <div class="h-full">
    @livewire('dashboard.' . $slug)
  </div>
  @livewireScripts
</body>
</html>

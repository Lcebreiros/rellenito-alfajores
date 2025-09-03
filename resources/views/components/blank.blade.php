<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('app.name') }}</title>
  @livewireStyles
  {{-- No cargamos tu header/sidebar ni CSS del panel.
       Si necesitás Tailwind aquí, podrías incluir tu CSS compilado. --}}
  <style>
    html,body{background:#0b1220; margin:0;} /* oscuro detrás del ticket, opcional */
  </style>
</head>
<body>
  {{ $slot }}
  @livewireScripts
</body>
</html>

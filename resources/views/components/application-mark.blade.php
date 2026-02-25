@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;

    // Fallback por defecto
    $src = asset('gestior_blanco.png');

    if (Auth::check() && Auth::user()->app_logo_path) {
        $path = Auth::user()->app_logo_path;

        if (Storage::disk('public')->exists($path)) {
            $url = Storage::disk('public')->url($path);

            // Cache-bust simple usando la fecha de modificación del archivo
            try {
                $v = Storage::disk('public')->lastModified($path);
            } catch (\Throwable $e) {
                $v = time();
            }

            $src = $url.'?v='.$v;
        }
    }
@endphp

<img
  src="{{ $src }}"
  alt="{{ config('app.name', 'Gestior') }}"
  {{ $attributes->merge(['class' => 'h-9 w-auto']) }}
  loading="lazy"
  decoding="async"
/>

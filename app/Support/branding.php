<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Branding
{
    /** Devuelve la URL del logo configurado o null si no hay */
    public static function logoUrl(): ?string
    {
        // 1) ENV directo
        if ($url = config('app.logo_url')) return $url;

        // 2) Archivo en storage si usas algo tipo “settings/logo.png”
        if (Storage::disk('public')->exists('settings/logo.png')) {
            return Storage::disk('public')->url('settings/logo.png');
        }

        // 3) null => se mostrará el nombre de la app
        return null;
    }

    public static function appName(): string
    {
        return config('app.name', 'Rellenito');
    }
}

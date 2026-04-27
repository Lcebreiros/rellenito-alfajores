<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales soportados por la aplicación.
     * Fuente de verdad única — usada también en LocaleController y tests.
     */
    public const SUPPORTED_LOCALES = ['es', 'en', 'pt'];

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Preferencia explícita guardada en sesión (el usuario cambió el idioma)
        $session = session('locale');
        if ($session && $this->isSupported($session)) {
            return $session;
        }

        // 2. Cookie de preferencia persistente
        $cookie = $request->cookie('locale');
        if ($cookie && $this->isSupported($cookie)) {
            // Sincronizar a sesión para evitar leer la cookie en cada request
            session(['locale' => $cookie]);
            return $cookie;
        }

        // 3. Cabecera Accept-Language del navegador
        $browser = $this->parseBrowserLocale($request->header('Accept-Language', ''));
        if ($browser) {
            return $browser;
        }

        // 4. Valor por defecto de la aplicación
        return config('app.locale', 'es');
    }

    private function parseBrowserLocale(string $header): ?string
    {
        if (empty($header)) {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $lang = strtolower(trim(explode(';', $part)[0]));
            $short = substr($lang, 0, 2);

            if ($this->isSupported($short)) {
                return $short;
            }
        }

        return null;
    }

    private function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES, true);
    }
}

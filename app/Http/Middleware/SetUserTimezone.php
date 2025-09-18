<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SetUserTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $default = config('app.timezone', 'UTC'); // ya viene del .env si hiciste el paso 1

        $tz = $user?->timezone ?: $default;

        if ($tz) {
            try {
                date_default_timezone_set($tz);
                config(['app.timezone' => $tz]);
            } catch (\Throwable $e) {
                // opcional: log para depurar
                Log::warning('SetUserTimezone invalid TZ', ['tz' => $tz, 'err' => $e->getMessage()]);
            }
        }

        return $next($request);
    }
}

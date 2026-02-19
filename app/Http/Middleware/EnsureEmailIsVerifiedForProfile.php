<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as BaseMiddleware;
use Illuminate\Http\Request;

class EnsureEmailIsVerifiedForProfile extends BaseMiddleware
{
    /**
     * Rutas de edición de perfil que no requieren email verificado.
     * El usuario puede corregir su email sin haber verificado el anterior.
     */
    private const PROFILE_PATHS = [
        'user/profile',
        'user/profile-information',
        'user/password',
    ];

    public function handle(Request $request, Closure $next, ...$guards): mixed
    {
        foreach (self::PROFILE_PATHS as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                return $next($request);
            }
        }

        return parent::handle($request, $next, ...$guards);
    }
}

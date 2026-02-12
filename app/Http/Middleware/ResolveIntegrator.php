<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResolveIntegrator
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Por defecto es contexto interno
        $integrator = 'internal';

        // Si el token tiene ability específica para integraciones externas, marcarlo
        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            if ($token->can('storefront') || $token->can('external')) {
                $integrator = 'storefront';
            }
        }

        $request->attributes->set('integrator', $integrator);

        return $next($request);
    }
}

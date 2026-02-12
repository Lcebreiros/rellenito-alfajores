<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireInternalIntegrator
{
    public function handle(Request $request, Closure $next)
    {
        $integrator = $request->attributes->get('integrator', 'internal');

        if ($integrator === 'storefront') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso restringido para este token.',
            ], 403);
        }

        return $next($request);
    }
}

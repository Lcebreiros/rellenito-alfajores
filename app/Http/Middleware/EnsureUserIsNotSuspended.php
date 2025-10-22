<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay usuario, seguimos (esto middleware se suele aplicar a rutas auth)
        if (! $user) {
            return $next($request);
        }

        if ($user->is_suspended) {
            // Si es petición AJAX/JSON devolvemos 403 con mensaje
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cuenta suspendida'], 403);
            }

            // Redirigir a la pantalla de suspendido (podemos pasar razón y URL de apelación)
            return redirect()->route('auth.suspended')
                             ->with('suspended_reason', $user->suspended_reason ?? null);
        }

        return $next($request);
    }
}

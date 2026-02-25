<?php

namespace App\Http\Middleware;

use App\Models\ParkingShift;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckParkingShiftOpen
{
    /**
     * Valida que exista un turno de parking abierto antes de permitir operaciones.
     * Este middleware debe aplicarse a rutas que requieran un turno activo.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = $this->getCurrentCompanyId($user);

        $openShift = ParkingShift::where('company_id', $companyId)
            ->whereNull('ended_at')
            ->exists();

        if (!$openShift) {
            return back()->withErrors('No hay un turno de caja abierto. Debe iniciar un turno antes de realizar operaciones.');
        }

        return $next($request);
    }

    private function getCurrentCompanyId($user): int
    {
        if ($user && method_exists($user, 'isCompany') && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) $user->id;
    }
}

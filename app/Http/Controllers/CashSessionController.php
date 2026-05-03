<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CashSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Vista principal: empresa ve todas las sesiones de sus usuarios */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->isCompany()) {
            $sessions = CashSession::where('company_id', $user->id)
                ->with('user:id,name,email')
                ->latest('opened_at')
                ->paginate(25);
        } elseif ($user->isAdmin()) {
            $sessions = CashSession::where('user_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    // empleados de esta sucursal
                    $q->whereHas('user', fn($u) => $u->where('parent_id', $user->id));
                })
                ->with('user:id,name,email')
                ->latest('opened_at')
                ->paginate(25);
        } else {
            // usuario normal: solo sus propias sesiones
            $sessions = CashSession::where('user_id', $user->id)
                ->latest('opened_at')
                ->paginate(25);
        }

        return view('cash.index', compact('sessions'));
    }

    /** Detalle de una sesión con todos sus movimientos */
    public function show(CashSession $cashSession): View
    {
        $this->authorizeSession($cashSession);

        $cashSession->load(['user:id,name,email', 'movements' => function ($q) {
            $q->with('creator:id,name')->latest();
        }]);

        return view('cash.show', compact('cashSession'));
    }

    /** Abrir caja (POST) */
    public function open(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Solo un turno abierto por usuario a la vez
        if (CashSession::activeFor($user->id)) {
            return back()->withErrors(['error' => __('cash.already_open')]);
        }

        $data = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $companyId = $this->resolveCompanyId($user);

        $session = CashSession::create([
            'user_id'        => $user->id,
            'company_id'     => $companyId,
            'opening_amount' => $data['opening_amount'],
            'status'         => 'open',
            'opened_at'      => now(),
        ]);

        // Registrar el monto inicial como movimiento de apertura
        if ($data['opening_amount'] > 0) {
            CashMovement::create([
                'cash_session_id' => $session->id,
                'company_id'      => $companyId,
                'created_by'      => $user->id,
                'type'            => 'apertura',
                'amount'          => $data['opening_amount'],
                'description'     => __('cash.opening_movement'),
            ]);
        }

        return back()->with('ok', __('cash.opened_ok'));
    }

    /** Cerrar caja (POST) */
    public function close(Request $request, CashSession $cashSession): RedirectResponse
    {
        $this->authorizeSession($cashSession);

        if (!$cashSession->isOpen()) {
            return back()->withErrors(['error' => __('cash.already_closed')]);
        }

        $data = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'closing_note'   => ['nullable', 'string', 'max:500'],
        ]);

        $cashSession->update([
            'status'         => 'closed',
            'closing_amount' => $data['closing_amount'],
            'closing_note'   => $data['closing_note'] ?? null,
            'closed_at'      => now(),
        ]);

        return redirect()->route('cash.show', $cashSession)->with('ok', __('cash.closed_ok'));
    }

    /** Agregar ingreso o egreso manual (POST) */
    public function addMovement(Request $request, CashSession $cashSession): RedirectResponse
    {
        $this->authorizeSession($cashSession);

        if (!$cashSession->isOpen()) {
            return back()->withErrors(['error' => __('cash.session_closed')]);
        }

        $data = $request->validate([
            'type'        => ['required', 'in:ingreso,egreso'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        CashMovement::create([
            'cash_session_id' => $cashSession->id,
            'company_id'      => $cashSession->company_id,
            'created_by'      => auth()->id(),
            'type'            => $data['type'],
            'amount'          => $data['amount'],
            'description'     => $data['description'],
        ]);

        return back()->with('ok', __('cash.movement_added'));
    }

    private function authorizeSession(CashSession $session): void
    {
        $user = auth()->user();

        if ($user->isMaster()) return;

        if ($user->isCompany() && $session->company_id === $user->id) return;

        if ($session->user_id === $user->id) return;

        // Admin puede ver sesiones de su sucursal
        if ($user->isAdmin()) {
            $sessionUser = \App\Models\User::find($session->user_id);
            if ($sessionUser && $sessionUser->parent_id === $user->id) return;
        }

        abort(403);
    }

    private function resolveCompanyId(\App\Models\User $user): int
    {
        if ($user->isCompany()) return $user->id;
        if ($user->isAdmin() && $user->parent_id) return $user->parent_id;

        // usuario normal: subir hasta la company
        $current = $user;
        while ($current->parent_id) {
            $current = \App\Models\User::find($current->parent_id);
            if ($current?->isCompany()) return $current->id;
        }

        return $user->id;
    }
}

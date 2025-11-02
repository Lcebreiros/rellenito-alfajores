<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationHistory;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService
    ) {
        $this->middleware('auth');

        // Solo usuarios master pueden acceder
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (! $user || ! method_exists($user, 'isMaster') || ! $user->isMaster()) {
                abort(403, 'Acceso denegado');
            }
            return $next($request);
        });
    }

    /**
     * Panel unificado: form arriba + listado abajo (manage view).
     */
    public function index(Request $request): View
    {
        $query = Invitation::with(['creator', 'user'])
            ->where('created_by', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filtros simples
        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('subscription')) {
            $query->ofSubscription($request->input('subscription'));
        }

        $invitations = $query->paginate(15);

        // Estadísticas
        $stats = $this->invitationService->getInvitationStats(Auth::id());

        // Tipos y subs para el selector del form
        $types = Invitation::getTypeLabels();
        $subscriptions = Invitation::getValidSubscriptionLevels();

        // NOTA: se recomienda mover clearOldPlainKeys a un comando programado (scheduler).
        $this->clearOldPlainKeys();

        // Vista unificada (manage)
        return view('master.invitations.manage', compact('invitations', 'stats', 'types', 'subscriptions'));
    }

    /**
     * Crear nueva invitación (form integrado en index/manage).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invitation_type' => 'required|in:' . implode(',', Invitation::getValidTypes()),
            'subscription_level' => 'nullable|in:' . implode(',', Invitation::getValidSubscriptionLevels()),
            'max_users' => 'nullable|integer|min:1|max:1000',
            'expires_in_hours' => 'nullable|integer|min:1|max:8760',
            'notes' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            $result = $this->invitationService->createInvitation(
                masterUserId: Auth::id(),
                invitationType: $validated['invitation_type'],
                subscriptionLevel: $validated['subscription_level'] ?? null,
                permissions: $validated['permissions'] ?? null,
                maxUsers: $validated['max_users'] ?? null,
                expiresInHours: $validated['expires_in_hours'] ?? 72,
                notes: $validated['notes'] ?? null
            );

            DB::commit();

            $invitation = $result['invitation'];
            $plainKey = $result['plain_key'];

            Log::info('Master creó invitación', [
                'master_id' => Auth::id(),
                'invitation_id' => $invitation->id,
                'type' => $invitation->invitation_type,
            ]);

            // Redirigir al mismo manage (index) y mostrar modal con plain_key a través de flash
            return redirect()
                ->route('master.invitations.index')
                ->with('success', 'Invitación creada exitosamente')
                ->with('plain_key', $plainKey);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error creando invitación', [
                'master_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Error al crear la invitación. Intenta nuevamente.'])
                ->withInput();
        }
    }

    /**
     * Mostrar detalles (puede seguir existiendo si querés detalle aparte).
     */
    public function show(Invitation $invitation): View
    {
        $this->ensureOwnership($invitation);

        $invitation->load(['creator', 'user']);

        // Sincronizar expiración en lectura
        $invitation->checkAndMarkExpired();

        return view('master.invitations.show', compact('invitation'));
    }

    /**
     * Revocar invitación.
     */
    public function revoke(Invitation $invitation): RedirectResponse
    {
        $this->ensureOwnership($invitation);

        if ($this->invitationService->revokeInvitation($invitation)) {
            return back()->with('success', 'Invitación revocada exitosamente');
        }

        return back()->withErrors(['error' => 'No se puede revocar esta invitación']);
    }

    /**
     * Regenerar: revoca la invitación dada y crea una nueva con la misma configuración.
     * Devuelve la nueva plain_key en flash para mostrar en modal.
     */
    public function regenerate(Invitation $invitation): RedirectResponse
    {
        $this->ensureOwnership($invitation);

        // Solo podemos regenerar si estaba pendiente (no usada)
        if ($invitation->status !== Invitation::STATUS_PENDING) {
            return back()->withErrors(['error' => 'Solo se pueden regenerar invitaciones pendientes.']);
        }

        DB::beginTransaction();
        try {
            // Extraer configuración existente
            $type = $invitation->invitation_type;
            $subscription = $invitation->subscription_level;
            $permissions = $invitation->permissions;
            $maxUsers = $invitation->max_users;

            // Calculamos horas restantes: si expires_at existe, usar la diferencia en horas (mín 1)
            $expiresInHours = 72;
            if ($invitation->expires_at) {
                $diff = now()->diffInHours($invitation->expires_at, false);
                $expiresInHours = max(1, $diff);
            }

            // Primero revocamos la antigua
            $revoked = $this->invitationService->revokeInvitation($invitation);
            if (! $revoked) {
                DB::rollBack();
                return back()->withErrors(['error' => 'No se pudo revocar la invitación anterior.']);
            }

            // Crear nueva invitación con la misma configuración
            $result = $this->invitationService->createInvitation(
                masterUserId: Auth::id(),
                invitationType: $type,
                subscriptionLevel: $subscription,
                permissions: $permissions,
                maxUsers: $maxUsers,
                expiresInHours: $expiresInHours,
                notes: $invitation->notes
            );

            DB::commit();

            $newInvitation = $result['invitation'];
            $plainKey = $result['plain_key'];

            Log::info('Master regeneró invitación', [
                'master_id' => Auth::id(),
                'old_invitation_id' => $invitation->id,
                'new_invitation_id' => $newInvitation->id,
            ]);

            return redirect()
                ->route('master.invitations.index')
                ->with('success', 'Invitación regenerada correctamente')
                ->with('plain_key', $plainKey);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error regenerando invitación', [
                'master_id' => Auth::id(),
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Error al regenerar la invitación.']);
        }
    }

    /**
     * Obtener estadísticas vía AJAX
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $stats = $this->invitationService->getInvitationStats(Auth::id());
        return response()->json($stats);
    }

    /**
     * Consumir/validar invitación (para uso desde otros sistemas o flujos)
     * 
     * ⚠️ NOTA: En Gestior, esto se maneja desde SubscriptionController
     * Este método es para otros flujos dentro de rellenito-alfajores
     */
    public function consume(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|min:10',
        ]);

        $plainKey = strtoupper($request->input('code'));

        return DB::transaction(function () use ($plainKey) {
            // ✅ Usar el servicio para buscar y validar
            $invitation = $this->invitationService->findAndValidateInvitation($plainKey);

            if (!$invitation) {
                return redirect()->back()
                    ->withErrors(['code' => 'Código de invitación inválido o expirado.']);
            }

            $user = Auth::user();

            if (!$user) {
                return redirect()->back()
                    ->withErrors(['code' => 'Debes iniciar sesión primero.']);
            }

            // Marcar como usada
            $used = $this->invitationService->useInvitation($invitation, $user->id);

            if (!$used) {
                return redirect()->back()
                    ->withErrors(['code' => 'Este código ya fue utilizado.']);
            }

            // Guardar en historial
            InvitationHistory::create([
                'invitation_id' => $invitation->id,
                'key' => substr($plainKey, 0, 8) . '***', // Solo primeros 8 caracteres
                'email' => $user->email,
                'notes' => $invitation->notes,
                'used_at' => now(),
                'used_by' => $user->id,
                'payload' => $invitation->toArray(),
            ]);

            Log::info('Invitación consumida', [
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'subscription_level' => $invitation->subscription_level,
            ]);

            return redirect()->route('home')
                ->with('success', 'Código de invitación aplicado correctamente.');
        }, 5); // reintentos DB
    }

    /**
     * Asegura que la invitación pertenezca al master actual.
     */
    protected function ensureOwnership(Invitation $invitation): void
    {
        if ($invitation->created_by !== Auth::id()) {
            abort(404);
        }
    }

    /**
     * Limpiar keys plain de invitaciones vistas hace más de 5 minutos.
     * (Mejor mover esto a un comando programado).
     */
    private function clearOldPlainKeys(): void
    {
        try {
            Invitation::where('created_by', Auth::id())
                ->whereNotNull('key_plain')
                ->where('created_at', '<', now()->subMinutes(5))
                ->update(['key_plain' => null]);
        } catch (\Throwable $e) {
            Log::warning('Error limpiando keys plain viejas', ['error' => $e->getMessage()]);
        }
    }
}
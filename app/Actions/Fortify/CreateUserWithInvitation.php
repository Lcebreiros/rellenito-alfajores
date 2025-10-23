<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Actions\Fortify\CreateNewUser;
use App\Services\InvitationService;
use App\Models\Invitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateUserWithInvitation implements CreatesNewUsers
{
    public function __construct(
        protected CreateNewUser $createNewUser,
        protected InvitationService $invitationService
    ) {}

    public function create(array $input)
    {
        // 1) Validar que existe la key en input
        if (empty($input['invitation_key'])) {
            throw ValidationException::withMessages([
                'invitation_key' => ['El código de invitación es requerido.'],
            ]);
        }

        // 2) Buscar y validar invitación (ya normaliza internamente)
        $invitation = $this->invitationService->findAndValidateInvitation($input['invitation_key']);

        if (!$invitation) {
            throw ValidationException::withMessages([
                'invitation_key' => ['Código de invitación inválido o expirado.'],
            ]);
        }

        // 3) Crear usuario dentro de transacción
        return DB::transaction(function () use ($input, $invitation) {
            try {
                // Crear usuario base
                $user = $this->createNewUser->create($input);

                // Asegurar que existan los roles base (por si no se corrió el seeder en prod)
                $this->ensureBaseRoles();

                // Asignar datos específicos de la invitación
                $this->applyInvitationToUser($user, $invitation);

                // Marcar invitación como usada
                if (!$this->invitationService->useInvitation($invitation, $user->id)) {
                    throw new RuntimeException('Error al procesar la invitación.');
                }

                // Eventos
                event(new Registered($user));
                Auth::login($user);

                Log::info('Usuario creado con invitación exitosamente', [
                    'user_id' => $user->id,
                    'invitation_id' => $invitation->id,
                    'invitation_type' => $invitation->invitation_type
                ]);

                return $user;

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Errores de validación (ej: email ya usado) deben mostrarse al usuario
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Error creando usuario con invitación', [
                    'error' => $e->getMessage(),
                    'invitation_id' => $invitation->id,
                    'invitation_type' => $invitation->invitation_type ?? 'unknown'
                ]);
                
                throw new RuntimeException('No se pudo completar el registro. Intenta nuevamente.');
            }
        });
    }

    /**
     * Aplica configuración de la invitación al usuario
     */
    private function applyInvitationToUser($user, Invitation $invitation): void
    {
        match($invitation->invitation_type) {
            Invitation::TYPE_COMPANY => $this->setupCompanyUser($user, $invitation),
            Invitation::TYPE_ADMIN => $this->setupAdminUser($user, $invitation),
            Invitation::TYPE_USER => $this->setupRegularUser($user, $invitation),
            default => throw new RuntimeException("Tipo de invitación desconocido: {$invitation->invitation_type}")
        };
    }

    /**
     * Crea de forma idempotente los roles base si no existen (guard web)
     */
    private function ensureBaseRoles(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        foreach (['master', 'company', 'admin', 'user'] as $name) {
            Role::findOrCreate($name, $guard);
        }
    }

    private function setupCompanyUser($user, Invitation $invitation): void
    {
        $user->update([
            'hierarchy_level' => User::HIERARCHY_COMPANY,
            'parent_id' => $invitation->created_by,
        ]);
        // Asignar rol con Spatie
        $user->assignRole('company');

        // Aplicar plan de suscripción y límites
        $this->applyPlanToUser($user, $invitation->subscription_level);
    }

    private function setupAdminUser($user, Invitation $invitation): void
    {
        $user->update([
            'hierarchy_level' => User::HIERARCHY_ADMIN,
            'parent_id' => $invitation->created_by,
        ]);
        $user->assignRole('admin');
        $this->applyPlanToUser($user, $invitation->subscription_level);
    }

    private function setupRegularUser($user, Invitation $invitation): void
    {
        $user->update([
            'hierarchy_level' => User::HIERARCHY_USER, 
            'parent_id' => $invitation->created_by,
        ]);
        $user->assignRole('user');
        $this->applyPlanToUser($user, $invitation->subscription_level);
    }

    /**
     * Establece nivel de suscripción y límites según plan.
     * - Para usuarios empresa: define branch_limit y user_limit
     * - Para otros: solo persiste subscription_level (informativo)
     */
    private function applyPlanToUser(User $user, ?string $level): void
    {
        $level = $level ?: 'basic';

        // Siempre guardar el nivel de suscripción informado
        $user->subscription_level = $level;

        // Si es empresa, aplicar límites del plan
        if ($user->hierarchy_level === User::HIERARCHY_COMPANY) {
            switch ($level) {
                case 'basic':
                    $user->branch_limit = 1;   // 1 sucursal
                    $user->user_limit   = 3;   // hasta 3 usuarios
                    break;
                case 'premium':
                    $user->branch_limit = 5;   // hasta 5 sucursales
                    $user->user_limit   = 10;  // hasta 10 usuarios
                    break;
                case 'enterprise':
                default:
                    // ilimitado => null (sin tope)
                    $user->branch_limit = null;
                    $user->user_limit   = null;
                    break;
            }
        }

        $user->save();
    }
}

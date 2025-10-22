<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;

class InvitationService
{
    // Tipos válidos de invitación
    const VALID_TYPES = [
        Invitation::TYPE_COMPANY,
        Invitation::TYPE_ADMIN, 
        Invitation::TYPE_USER
    ];

    // Niveles de suscripción válidos
    const VALID_SUBSCRIPTION_LEVELS = [
        'basic',
        'premium', 
        'enterprise'
    ];

    /**
     * Genera una key "amigable" tipo AAAA-BBBB-1111
     */
    public function generateKeyString(int $length = 12): string
    {
        $raw = strtoupper(Str::random($length));
        // Formatear en bloques de 4 para que se copie fácil
        return trim(chunk_split($raw, 4, '-'), '-');
    }

    /**
     * Crea una nueva invitación
     */
    public function createInvitation(
    int $masterUserId, 
    string $invitationType, 
    ?string $subscriptionLevel = null,
    ?array $permissions = null,
    ?int $maxUsers = null,
    int $expiresInHours = 72,
    ?string $notes = null
): array { // Cambiar a array para devolver key también
    // Validaciones
    $this->validateInvitationType($invitationType);
    
    if ($subscriptionLevel && !in_array($subscriptionLevel, self::VALID_SUBSCRIPTION_LEVELS)) {
        throw new Exception("Nivel de suscripción inválido: {$subscriptionLevel}");
    }

    // Generar key única
    do {
        $plainKey = $this->generateKeyString(12);
        $keyHash = Hash::make($plainKey);
        $fingerprint = $this->computeFingerprint($plainKey); // Agregar fingerprint
        $exists = Invitation::where('key_fingerprint', $fingerprint)->exists();
    } while ($exists);

    // Configurar defaults por tipo
    $config = $this->getDefaultConfigForType($invitationType);
    
    $invitation = Invitation::create([
        'created_by' => $masterUserId,
        'invitation_type' => $invitationType,
        'subscription_level' => $subscriptionLevel ?? $config['subscription_level'],
        'permissions' => $permissions ?? $config['permissions'],
        'key_hash' => $keyHash,
        'key_fingerprint' => $fingerprint, // AGREGAR ESTO
        'key_plain' => $plainKey,
        'expires_at' => Carbon::now()->addHours($expiresInHours),
        'max_users' => $maxUsers ?? $config['max_users'],
        'notes' => $notes,
        'status' => Invitation::STATUS_PENDING, // AGREGAR STATUS
    ]);

    return ['invitation' => $invitation, 'plain_key' => $plainKey]; // Devolver ambos
}

// Agregar método fingerprint
private function computeFingerprint(string $plainKey): string
{
    $appKey = config('app.key') ?? env('APP_KEY');
    if (empty($appKey)) {
        throw new Exception('APP_KEY no configurada');
    }
    return hash_hmac('sha256', $plainKey, $appKey);
}

    /**
     * Valida una key contra una invitación
     */
    public function validateKey(string $plainKey, Invitation $invitation): bool
    {
        return Hash::check($plainKey, $invitation->key_hash);
    }

    /**
     * Busca invitación por key y valida
     */
    public function findAndValidateInvitation(string $plainKey): ?Invitation
    {
        $invitations = Invitation::where('status', Invitation::STATUS_PENDING)->get();
        
        foreach ($invitations as $invitation) {
            if ($this->validateKey($plainKey, $invitation)) {
                // Verificar si no ha expirado
                if ($invitation->expires_at && $invitation->expires_at->isPast()) {
                    $invitation->update(['status' => Invitation::STATUS_EXPIRED]);
                    return null;
                }
                
                return $invitation;
            }
        }
        
        return null;
    }

    /**
     * Marca invitación como usada
     */
    public function useInvitation(Invitation $invitation, int $userId): bool
    {
        if ($invitation->status !== Invitation::STATUS_PENDING) {
            return false;
        }

        $invitation->update([
            'status' => Invitation::STATUS_USED,
            'used_at' => Carbon::now(),
            'used_by' => $userId,
            'key_plain' => null, // Limpiar key por seguridad
        ]);

        return true;
    }

    /**
     * Revoca una invitación
     */
    public function revokeInvitation(Invitation $invitation): bool
    {
        if ($invitation->status === Invitation::STATUS_USED) {
            return false;
        }

        $invitation->update([
            'status' => Invitation::STATUS_REVOKED,
            'key_plain' => null,
        ]);

        return true;
    }

    /**
     * Limpia keys plain de invitaciones mostradas
     */
    public function clearPlainKey(Invitation $invitation): void
    {
        $invitation->update(['key_plain' => null]);
    }

    /**
     * Configuración default por tipo de invitación
     */
    private function getDefaultConfigForType(string $type): array
    {
        $configs = [
            Invitation::TYPE_COMPANY => [
                'subscription_level' => 'basic',
                'permissions' => ['create_users', 'manage_company'],
                'max_users' => 10,
            ],
            Invitation::TYPE_ADMIN => [
                'subscription_level' => 'premium',
                'permissions' => ['manage_users', 'view_reports'],
                'max_users' => null,
            ],
            Invitation::TYPE_USER => [
                'subscription_level' => 'basic',
                'permissions' => ['basic_access'],
                'max_users' => null,
            ],
        ];

        return $configs[$type] ?? [];
    }

    /**
     * Valida tipo de invitación
     */
    private function validateInvitationType(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new Exception("Tipo de invitación inválido: {$type}");
        }
    }

    /**
     * Obtiene estadísticas de invitaciones
     */
    public function getInvitationStats(int $masterUserId): array
    {
        $invitations = Invitation::where('created_by', $masterUserId);
        
        return [
            'total' => $invitations->count(),
            'pending' => $invitations->where('status', Invitation::STATUS_PENDING)->count(),
            'used' => $invitations->where('status', Invitation::STATUS_USED)->count(),
            'expired' => $invitations->where('status', Invitation::STATUS_EXPIRED)->count(),
            'revoked' => $invitations->where('status', Invitation::STATUS_REVOKED)->count(),
        ];
    }
    public function archiveAndDelete(Invitation $inv, ?User $user = null): InvitationHistory
    {
        return DB::transaction(function () use ($inv, $user) {
            $payload = $inv->toArray();

            $history = InvitationHistory::create([
                'invitation_id' => $inv->id,
                'key' => $inv->key,
                'email' => $inv->email,
                'notes' => $inv->notes ?? null,
                'used_at' => now(),
                'used_by' => $user?->id,
                'payload' => $payload,
            ]);

            $inv->delete();

            return $history;
        });
    }
}
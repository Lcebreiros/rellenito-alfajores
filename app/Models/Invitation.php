<?php

// app/Models/Invitation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Invitation extends Model
{
    use HasFactory;

    // Constantes para tipos de invitación
    public const TYPE_COMPANY = 'company';
    public const TYPE_ADMIN = 'admin';
    public const TYPE_USER = 'user';

    // Constantes para estados
    public const STATUS_PENDING = 'pending';
    public const STATUS_USED = 'used';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_EXPIRED = 'expired';

    // Constantes para niveles de suscripción
    public const SUBSCRIPTION_BASIC = 'basic';
    public const SUBSCRIPTION_PREMIUM = 'premium';
    public const SUBSCRIPTION_ENTERPRISE = 'enterprise';

    protected $fillable = [
        'created_by',
        'invitation_type',
        'subscription_level',
        'permissions',
        'key_hash',
        'key_fingerprint',
        'key_plain',
        'expires_at',
        'used_at',
        'used_by',
        'status',
        'max_users',
        'notes',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'max_users' => 'integer',
        'created_by' => 'integer',
        'used_by' => 'integer',
    ];

    protected $hidden = [
        'key_hash',
        'key_fingerprint',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Usuario master que creó la invitación
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que usó la invitación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope para invitaciones pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para invitaciones usadas
     */
    public function scopeUsed($query)
    {
        return $query->where('status', self::STATUS_USED);
    }

    /**
     * Scope para invitaciones no expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope por tipo de invitación
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('invitation_type', $type);
    }

    /**
     * Scope por nivel de suscripción
     */
    public function scopeOfSubscription($query, string $level)
    {
        return $query->where('subscription_level', $level);
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Accessor para mostrar el estado de manera amigable
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_USED => 'Usada',
            self::STATUS_REVOKED => 'Revocada',
            self::STATUS_EXPIRED => 'Expirada',
            default => 'Desconocido',
        };
    }

    /**
     * Accessor para mostrar el tipo de manera amigable
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->invitation_type) {
            self::TYPE_COMPANY => 'Empresa',
            self::TYPE_ADMIN => 'Administrador',
            self::TYPE_USER => 'Usuario',
            default => 'Desconocido',
        };
    }

    /**
     * Accessor para verificar si la key plain está disponible
     */
    public function getHasPlainKeyAttribute(): bool
    {
        return !is_null($this->key_plain);
    }

    // ========================================
    // MÉTODOS DE UTILIDAD
    // ========================================

    /**
     * Verifica si la invitación ha expirado
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }

    /**
     * Verifica si la invitación está disponible para usar
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Verifica si tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!is_array($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions, true);
    }

    /**
     * Obtiene los días restantes para expirar
     */
    public function getDaysToExpire(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        $now = Carbon::now();
        
        if ($this->expires_at->isPast()) {
            return 0;
        }

        return (int) $now->diffInDays($this->expires_at);
    }

    /**
     * Marca automáticamente como expirada si corresponde
     */
    public function checkAndMarkExpired(): bool
    {
        if ($this->status === self::STATUS_PENDING && $this->isExpired()) {
            $this->update(['status' => self::STATUS_EXPIRED]);
            return true;
        }

        return false;
    }

    // ========================================
    // ARRAYS ESTÁTICOS ÚTILES
    // ========================================

    /**
     * Obtiene todos los tipos válidos
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_COMPANY,
            self::TYPE_ADMIN,
            self::TYPE_USER,
        ];
    }

    /**
     * Obtiene todos los estados válidos
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_USED,
            self::STATUS_REVOKED,
            self::STATUS_EXPIRED,
        ];
    }

    /**
     * Obtiene todos los niveles de suscripción válidos
     */
    public static function getValidSubscriptionLevels(): array
    {
        return [
            self::SUBSCRIPTION_BASIC,
            self::SUBSCRIPTION_PREMIUM,
            self::SUBSCRIPTION_ENTERPRISE,
        ];
    }

    /**
     * Obtiene labels amigables para tipos
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_COMPANY => 'Empresa',
            self::TYPE_ADMIN => 'Administrador',
            self::TYPE_USER => 'Usuario',
        ];
    }

    /**
     * Obtiene labels amigables para estados
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_USED => 'Usada',
            self::STATUS_REVOKED => 'Revocada',
            self::STATUS_EXPIRED => 'Expirada',
        ];
    }
}
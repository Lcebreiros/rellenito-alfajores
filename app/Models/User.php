<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles, SoftDeletes;

    public const DEFAULT_APP_LOGO      = 'images/logo.png';
    // No usar public/; fallback debe ser images/Gestior.png en raíz (ruteado)
    public const DEFAULT_RECEIPT_LOGO  = 'images/Gestior.png';

    // Jerarquía
    public const HIERARCHY_MASTER  = -1;
    public const HIERARCHY_COMPANY = 0;
    public const HIERARCHY_ADMIN   = 1;
    public const HIERARCHY_USER    = 2;

    protected $fillable = [
        'name', 'email', 'password',
        'has_seen_welcome',
        'app_logo_path',
        'theme', 'site_title', 'receipt_logo_path',
        'parent_id',
        'hierarchy_level',
        'hierarchy_path',
        'is_active',
        'branch_limit',
        'user_limit',
        'subscription_level',
        'organization_context',
        // Campos para relación polimórfica
        'representable_id',
        'representable_type',
        // Configuraciones de notificaciones de stock
        'notify_low_stock',
        'low_stock_threshold',
        'notify_out_of_stock',
        'notify_by_email',
        // Google Calendar
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_calendar_id',
        'google_email',
        'google_calendar_sync_enabled',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'app_logo_url',
        'receipt_logo_url',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'has_seen_welcome'  => 'boolean',
        'is_active'         => 'boolean',
        'hierarchy_level'   => 'integer',
        'user_limit'        => 'integer',
        'subscription_level'=> 'string',
        'notify_low_stock'  => 'boolean',
        'low_stock_threshold' => 'integer',
        'notify_out_of_stock' => 'boolean',
        'notify_by_email' => 'boolean',
    ];

    // ================================
    // RELACIÓN POLIMÓRFICA
    // ================================
    public function representable(): MorphTo
    {
        return $this->morphTo();
    }

    // Helpers para identificar qué tipo de entidad representa
    public function isBranchUser(): bool
    {
        return $this->hierarchy_level === self::HIERARCHY_ADMIN && 
               $this->representable_type === Branch::class;
    }

    public function isCompanyUser(): bool
    {
        return $this->hierarchy_level === self::HIERARCHY_COMPANY &&
               $this->representable_type === Company::class;
    }

    // Acceso directo a la sucursal si este usuario la representa
    public function branch(): ?Branch
    {
        return $this->representable_type === Branch::class ? $this->representable : null;
    }

    // ================================
    // RELACIONES JERÁRQUICAS (sin cambios)
    // ================================
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id')->where('is_active', true);
    }

    // ================================
    // MÉTODOS DE JERARQUÍA (sin cambios)
    // ================================
    public function isMaster(): bool { return $this->hierarchy_level === self::HIERARCHY_MASTER; }
    public function isCompany(): bool { return $this->hierarchy_level === self::HIERARCHY_COMPANY; }
    public function isAdmin(): bool { return $this->hierarchy_level === self::HIERARCHY_ADMIN; }
    public function isRegularUser(): bool { return $this->hierarchy_level === self::HIERARCHY_USER; }

    // ================================
    // MÉTODOS DE SUCURSALES ACTUALIZADOS
    // ================================
    public function branches(): HasMany
    {
        return $this->children()
                   ->where('hierarchy_level', self::HIERARCHY_ADMIN)
                   ->where('representable_type', Branch::class);
    }

    public function canCreateBranch(): bool
    {
        if (!$this->isCompany()) return false;
        if ($this->branch_limit === null) return true;

        $currentBranches = $this->branches()->count();
        return $currentBranches < $this->branch_limit;
    }

    /**
     * Crea una sucursal con su User asociado
     */
    public function createBranchWithUser(array $branchData, array $userData): User
    {
        if (!$this->canCreateBranch()) {
            throw new \Exception('Se alcanzó el límite de sucursales permitido');
        }

        return DB::transaction(function () use ($branchData, $userData) {
            // 1. Crear el Branch (datos de negocio)
            $branch = Branch::create(array_merge($branchData, [
                'company_id' => $this->id,
            ]));

            // 2. Crear el User que representa la sucursal (autenticación)
            $user = static::create(array_merge($userData, [
                'parent_id' => $this->id,
                'hierarchy_level' => self::HIERARCHY_ADMIN,
                'organization_context' => $this->organization_context,
                'representable_id' => $branch->id,
                'representable_type' => Branch::class,
            ]));

            $user->updateHierarchyPath();

            return $user;
        });
    }

    // ================================
    // RESTO DE MÉTODOS (sin cambios)
    // ================================
    
    public function isDescendantOf(User $potentialAncestor): bool
    {
        if (!$this->hierarchy_path || !$potentialAncestor->hierarchy_path) {
            return false;
        }
        return Str::startsWith($this->hierarchy_path, rtrim($potentialAncestor->hierarchy_path, '/') . '/');
    }

    public function canManageUser(User $targetUser): bool
    {
        if ($this->isMaster()) return true;
        if ($this->id === $targetUser->id) return false;
        if ($targetUser->isMaster()) return false;
        if ($this->hierarchy_level >= $targetUser->hierarchy_level) return false;

        return $targetUser->isDescendantOf($this);
    }

    public function updateHierarchyPath(): void
    {
        DB::transaction(function () {
            if (!$this->parent_id) {
                $newPath = "/{$this->id}";
            } else {
                $parentPath = static::where('id', $this->parent_id)->value('hierarchy_path') ?? "/{$this->parent_id}";
                $newPath = rtrim($parentPath, '/') . "/{$this->id}";
            }

            $oldPath = $this->hierarchy_path;
            if ($oldPath === $newPath) {
                return;
            }

            static::withoutEvents(function () use ($newPath) {
                $this->hierarchy_path = $newPath;
                $this->saveQuietly();
            });

            if ($oldPath) {
                $sql = "UPDATE " . (new static)->getTable() .
                       " SET hierarchy_path = REPLACE(hierarchy_path, ?, ?)" .
                       " WHERE hierarchy_path LIKE ?";
                DB::update($sql, [$oldPath, $newPath, "{$oldPath}/%"]);
            }
        });
    }

    // Boot method
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->updateHierarchyPath();
        });

        static::updated(function (User $user) {
            if ($user->wasChanged('parent_id')) {
                $user->updateHierarchyPath();
                // Invalidar caché de jerarquía
                Cache::forget("user.{$user->id}.root_company");
            }

            if ($user->wasChanged(['parent_id', 'hierarchy_level'])) {
                // Invalidar caché también para hijos (por si afecta su jerarquía)
                $user->children()->each(function ($child) {
                    Cache::forget("user.{$child->id}.root_company");
                });
            }
        });

        static::deleted(function (User $user) {
            // Limpiar caché al eliminar
            Cache::forget("user.{$user->id}.root_company");
        });
    }

    // Logos methods (sin cambios)
    public function getAppLogoUrlAttribute(): string
    {
        if ($this->app_logo_path && Storage::disk('public')->exists($this->app_logo_path)) {
            $url = Storage::disk('public')->url($this->app_logo_path);
            $v   = Storage::disk('public')->lastModified($this->app_logo_path) ?: time();
            return "{$url}?v={$v}";
        }
        return asset(self::DEFAULT_APP_LOGO);
    }

    public function getReceiptLogoUrlAttribute(): string
    {
        // Solo exponemos la imagen de comprobante del propio usuario si existe
        if (auth()->id() === $this->id && $this->receipt_logo_path && Storage::disk('public')->exists($this->receipt_logo_path)) {
            $v = Storage::disk('public')->lastModified($this->receipt_logo_path) ?: time();
            return route('user.receipt-logo', ['v' => $v]);
        }

        // Fallback: logo por defecto "Gestior.png" desde raíz/images (nunca otro usuario)
        return route('branding.default-receipt');
    }
    public function rootCompany(): ?self
{
    // Usar caché para evitar queries repetidas (60 minutos)
    return Cache::remember(
        "user.{$this->id}.root_company",
        3600,
        function () {
            // si ya es company
            if (method_exists($this, 'isCompany') && $this->isCompany()) {
                return $this;
            }

            // Si tiene relación parent, recorrerla (evita queries innecesarias en bucle)
            $current = $this->parent ?? $this;
            $visited = [];

            while ($current) {
                // evitar loops
                if (in_array($current->id, $visited, true)) break;
                $visited[] = $current->id;

                if (method_exists($current, 'isCompany') && $current->isCompany()) {
                    return $current;
                }

                // subir por el parent relation si existe
                if ($current->parent) {
                    $current = $current->parent;
                    continue;
                }

                // fallback a parent_id (por si parent no es relación cargada)
                if (!empty($current->parent_id)) {
                    $current = self::find($current->parent_id);
                    continue;
                }

                $current = null;
            }

            return null;
        }
    );
}
}

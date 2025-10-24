<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Class Branch
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $slug
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $contact_email
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'address',
        'phone',
        'contact_email',
        'logo_path',
        'is_active',
        'use_company_inventory',
    ];

    /**
     * Usar slug para route-model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ================================
    // RELACIONES
    // ================================
    
    /**
     * Usuario que representa esta sucursal (para autenticación)
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'representable');
    }

    /**
     * Empresa dueña de la sucursal
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Empleados que pertenecen a la sucursal
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Usuarios bajo esta sucursal (a través de su user representante)
     */
    public function users()
    {
        if ($this->user) {
            return $this->user->children()->where('hierarchy_level', User::HIERARCHY_USER);
        }
        return collect();
    }

    // ================================
    // MÉTODOS DE CONVENIENCIA
    // ================================

    /**
     * Email de acceso (del usuario que representa la sucursal)
     */
    public function getLoginEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Estado activo (del usuario que representa la sucursal)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->user?->is_active ?? false;
    }

    /**
     * Límite de usuarios que puede crear esta sucursal
     */
    public function getUserLimitAttribute(): ?int
    {
        return $this->user?->user_limit;
    }

    /**
     * Puede crear más usuarios
     */
    public function canCreateUsers(): bool
    {
        return $this->user?->canCreateUsers() ?? false;
    }

    /**
     * Contar usuarios actuales
     */
    public function getUsersCountAttribute(): int
    {
        return $this->user ? $this->user->children()->where('hierarchy_level', User::HIERARCHY_USER)->count() : 0;
    }

    /**
     * URL pública del logo de la sucursal
     */
    public function logoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    // ================================
    // SCOPES
    // ================================

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeOfCompany($query, User $company)
    {
        return $query->where('company_id', $company->id);
    }

    // ================================
    // BOOT
    // ================================

    protected static function booted()
    {
        static::creating(function (Branch $branch) {
            if (empty($branch->slug)) {
                $branch->slug = Str::slug($branch->name) . '-' . Str::random(6);
            }
        });

        // Cuando se elimina una sucursal, eliminar su usuario representante
        static::deleting(function (Branch $branch) {
            $branch->user?->delete();
        });
    }
}

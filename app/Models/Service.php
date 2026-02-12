<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'service_category_id',
        'name',
        'description',
        'tags',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'user_id' => 'integer',
        'company_id' => 'integer',
        'tags' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(User::class, 'company_id'); }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }

    /**
     * Relación con insumos usados en este servicio
     */
    public function supplies(): HasMany
    {
        return $this->hasMany(ServiceSupply::class);
    }

    /**
     * Variantes del servicio (duración/paquete/precio)
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ServiceVariant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableFor(Builder $query, User $user): Builder
    {
        if ($user->isMaster()) {
            return $query;
        }
        if ($user->isCompany()) {
            return $query->where('company_id', $user->id);
        }
        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $query->where(function ($q) use ($user, $company) {
                $q->where('user_id', $user->id)
                  ->orWhere('company_id', $company->id);
            });
        }
        // usuario regular: sus servicios o de su parent
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('user_id', $user->parent_id ?? 0);
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (!$service->company_id && $service->user) {
                $service->company_id = $service->user->rootCompany()?->id ?? $service->user_id;
            }
        });
    }

    // Permitir que company/master y sucursales vean servicios según jerarquía (similar a Product)
    public function resolveRouteBinding($value, $field = null)
    {
        $query = static::query()->withoutGlobalScope('byUser');
        if ($field) {
            $query->where($field, $value);
        } else {
            $query->where($this->getRouteKeyName(), $value);
        }

        $service = $query->firstOrFail();
        $user = auth()->user();
        if (!$user) abort(404);

        if (method_exists($user,'isMaster') && $user->isMaster()) return $service;

        if (method_exists($user,'isCompany') && $user->isCompany()) {
            if ((int)$service->company_id === (int)$user->id) return $service;
            abort(404);
        }

        // Dueño directo
        if ((int)$service->user_id === (int)$user->id) return $service;

        // Usuario hijo: puede ver los de su parent
        if (!empty($user->parent_id)) {
            if ((int)$service->user_id === (int)$user->parent_id) return $service;
        }

        // Sucursal con inventario de empresa: permitir ver servicios de company
        try {
            $branch = method_exists($user,'branch') ? $user->branch() : null;
            $company = method_exists($user,'rootCompany') ? $user->rootCompany() : null;
            if ($branch && (bool)($branch->use_company_inventory ?? false) && $company && (int)$service->company_id === (int)$company->id) {
                return $service;
            }
        } catch (\Throwable $e) {}

        abort(404);
    }
}

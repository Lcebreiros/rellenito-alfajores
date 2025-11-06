<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class PaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'is_global',
        'requires_gateway',
        'gateway_config',
        'gateway_provider',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'requires_gateway' => 'boolean',
        'gateway_config' => 'array',
        'sort_order' => 'integer',
    ];

    // ---------- RELACIONES ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_method')
            ->withPivot(['amount', 'reference', 'notes', 'gateway_response'])
            ->withTimestamps();
    }

    /**
     * Usuarios que han activado este método de pago global
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_payment_methods')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    // ---------- SCOPES ----------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        // Determinar el company_id a usar
        $companyId = null;

        if ($user->isCompany()) {
            // Si es company, usar su propio ID
            $companyId = $user->id;
        } elseif ($user->isMaster()) {
            // Si es master, intentar encontrar la primera empresa o usar métodos del primer user
            $companyId = \App\Models\User::where('hierarchy_level', \App\Models\User::HIERARCHY_COMPANY)
                ->value('id');
        } else {
            // Si es admin o usuario normal, buscar su company
            $companyId = Order::findRootCompanyId($user);
        }

        // Si encontramos un company_id, filtrar por él
        if ($companyId) {
            return $query->where('user_id', $companyId);
        }

        // Fallback: solo sus propios métodos
        return $query->where('user_id', $user->id);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true)->whereNull('user_id');
    }

    /**
     * Obtiene métodos disponibles para un usuario (globales activados + propios)
     */
    public function scopeAvailableForUser(Builder $query, User $user): Builder
    {
        return $query->where(function($q) use ($user) {
            // Métodos globales que el usuario ha activado
            $q->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id)
                      ->where('user_payment_methods.is_active', true);
            })
            // O métodos propios del usuario
            ->orWhere(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('is_active', true);
            });
        });
    }

    // ---------- HELPERS ----------

    /**
     * Verifica si este método de pago requiere procesamiento por pasarela
     */
    public function needsGatewayProcessing(): bool
    {
        return $this->requires_gateway && !empty($this->gateway_provider);
    }

    /**
     * Obtiene el ícono por defecto basado en el slug
     */
    public function getDefaultIcon(): string
    {
        return match($this->slug) {
            'cash' => 'banknotes',
            'transfer' => 'arrows-right-left',
            'mercadopago' => 'device-phone-mobile',
            'paypal' => 'credit-card',
            'card' => 'credit-card',
            'crypto' => 'circle-stack',
            default => 'currency-dollar',
        };
    }

    /**
     * Obtiene el ícono actual o el por defecto
     */
    public function getIcon(): string
    {
        return $this->icon ?: $this->getDefaultIcon();
    }

    /**
     * Obtiene el logo del método de pago si existe (imagen local)
     */
    public function getLogo(): ?string
    {
        // Buscar archivo PNG con el nombre del slug
        $logoPath = 'payment-logos/' . $this->slug . '.png';

        // Verificar si el archivo existe antes de retornarlo
        if (file_exists(public_path('images/' . $logoPath))) {
            return $logoPath;
        }

        return null;
    }

    /**
     * Verifica si tiene logo local disponible
     */
    public function hasLogo(): bool
    {
        return $this->getLogo() !== null;
    }
}
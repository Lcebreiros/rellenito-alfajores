<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'document_number',
        'company',
        'address',
        'city',
        'province',
        'country',
        'tags',
        'notes',
        'balance',
    ];

    protected $casts = [
        'tags'    => 'array',
        'balance' => 'decimal:2',
    ];

    // ---------- RELACIONES ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ---------- SCOPES ----------

    /**
     * Filtra clientes por usuario.
     * Si el usuario es master, puede ver todos.
     * Si no, solo ve sus propios clientes.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        // Si es master, puede ver todos los clientes
        if ($user->isMaster()) {
            return $query;
        }

        // Determinar el company_id a usar
        $companyId = null;

        if ($user->isCompany()) {
            // Si es company, usar su propio ID
            $companyId = $user->id;
        } else {
            // Si es admin o usuario normal, buscar su company
            $companyId = Order::findRootCompanyId($user);
        }

        // Filtrar por el company_id
        if ($companyId) {
            return $query->where('user_id', $companyId);
        }

        // Fallback: solo sus propios clientes
        return $query->where('user_id', $user->id);
    }
}

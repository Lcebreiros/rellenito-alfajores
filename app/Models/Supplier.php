<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación: Un proveedor tiene muchos insumos
     */
    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }

    /**
     * Relación: Un proveedor tiene muchos gastos
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(SupplierExpense::class);
    }
}

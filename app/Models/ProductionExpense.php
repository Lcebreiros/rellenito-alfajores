<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionExpense extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'expense_name',
        'description',
        'cost_per_unit',
        'quantity',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con el producto asociado
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula el costo total (costo por unidad * cantidad)
     */
    public function getTotalCostAttribute(): float
    {
        return $this->cost_per_unit * $this->quantity;
    }
}

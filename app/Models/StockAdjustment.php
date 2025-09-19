<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class StockAdjustment extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity_change', // cantidad agregada o restada
        'new_stock',       // 🔹 AGREGAR ESTA LÍNEA - stock resultante después del ajuste
        'reason',          // motivo del ajuste
        'reference_id',    // id polimórfico de referencia (orden, compra, etc.)
        'reference_type',  // tipo polimórfico
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relación polimórfica para saber a qué se asocia el ajuste
    public function reference()
    {
        return $this->morphTo();
    }
}
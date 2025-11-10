<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToUser;

class Supply extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'base_unit',          // 'g' | 'ml' | 'u'
        'stock_base_qty',     // en unidad base
        'avg_cost_per_base',  // $ por unidad base
    ];

    protected $casts = [
        'stock_base_qty'     => 'decimal:4',
        'avg_cost_per_base'  => 'decimal:6',
    ];

    protected $appends = ['formatted_stock'];

    public function purchases(): HasMany
    {
        return $this->hasMany(SupplyPurchase::class);
    }

    /**
     * Accessor: Formatea el stock eliminando decimales innecesarios
     */
    public function getFormattedStockAttribute(): string
    {
        $stock = (float) $this->stock_base_qty;

        // Si el stock es entero, mostrar sin decimales
        if ($stock == floor($stock)) {
            return number_format($stock, 0, ',', '.');
        }

        // Si tiene decimales, mostrar máximo 2 y eliminar ceros finales
        return rtrim(rtrim(number_format($stock, 2, ',', '.'), '0'), ',');
    }

    /**
     * Recalcula stock y costo promedio a partir de compras (qty * unit_to_base).
     */
    public function recomputeFromPurchases(): void
    {
        $agg = $this->purchases()
            ->selectRaw('COALESCE(SUM(qty * unit_to_base),0) as base_qty, COALESCE(SUM(total_cost),0) as total_cost')
            ->first();

        $totalBaseQty = (float) ($agg->base_qty ?? 0);   // en unidad base (g/ml/u)
        $totalCost    = (float) ($agg->total_cost ?? 0); // $

        $this->stock_base_qty    = $totalBaseQty;
        $this->avg_cost_per_base = $totalBaseQty > 0 ? ($totalCost / $totalBaseQty) : 0;
        $this->save();
    }
}

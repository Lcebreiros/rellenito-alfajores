<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
     * Alcance: insumos disponibles para un usuario (multi-tenant con jerarquía).
     * - Master: todos
     * - Company: propios (user_id = company.id)
     * - Branch (admin): si usa inventario de empresa -> insumos de la company; si no, propios
     * - Usuario regular: si su parent (branch) usa inventario de empresa -> insumos de la company; si no, del parent
     */
    public function scopeAvailableFor(Builder $query, \App\Models\User $user): Builder
    {
        $query = $query->withoutGlobalScope('byUser');

        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return $query;
        }

        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $query->where('user_id', $user->id);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $company = $user->rootCompany();
            $branch  = $user->branch();
            if ($branch && (bool)($branch->use_company_inventory ?? false)) {
                return $query->where('user_id', $company?->id ?? 0);
            }
            return $query->where('user_id', $user->id);
        }

        // Usuario regular: derivar del parent (branch)
        if (!empty($user->parent_id)) {
            $parent  = $user->parent;
            $company = $user->rootCompany();
            $parentBranch = $parent?->branch();
            if ($parentBranch && (bool)($parentBranch->use_company_inventory ?? false)) {
                return $query->where('user_id', $company?->id ?? 0);
            }
            return $query->where('user_id', $user->parent_id);
        }

        return $query->whereRaw('1 = 0');
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

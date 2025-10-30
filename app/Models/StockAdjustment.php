<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustment extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',           // Usuario que hizo el ajuste
        'product_id',        // Producto ajustado
        'quantity_change',   // Cantidad agregada o restada (+/-)
        'new_stock',         // Stock resultante después del ajuste
        'reason',            // Motivo del ajuste
        'reference_id',      // ID polimórfico de referencia
        'reference_type',    // Tipo polimórfico
        'notes',             // Notas adicionales
        'branch_id',         // Sucursal donde ocurrió el ajuste
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'new_stock' => 'decimal:2',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Usuario que realizó el ajuste
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Producto ajustado
     */
    public function product()
    {
        // Ver el producto incluso si pertenece a otro usuario (byUser) o está soft-deleted
        return $this->belongsTo(Product::class, 'product_id')
            ->withoutGlobalScope('byUser')
            ->withTrashed();
    }

    /**
     * Sucursal donde ocurrió el ajuste
     */
    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    /**
     * Referencia polimórfica (orden, compra, transferencia, etc.)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Ajustes de una sucursal específica
     */
    public function scopeOfBranch($query, $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Ajustes de una empresa (todas sus sucursales)
     */
    public function scopeOfCompany($query, User $company): Builder
    {
        $branchIds = $company->children()->pluck('id')->push($company->id);
        return $query->whereIn('branch_id', $branchIds);
    }

    /**
     * Solo entradas de stock
     */
    public function scopeEntries($query): Builder
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Solo salidas de stock
     */
    public function scopeExits($query): Builder
    {
        return $query->where('quantity_change', '<', 0);
    }

    /**
     * Por tipo de razón
     */
    public function scopeByReason($query, string $reason): Builder
    {
        return $query->where('reason', $reason);
    }

    /**
     * En un rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ================================
    // MÉTODOS DE NEGOCIO
    // ================================

    /**
     * ¿Es una entrada de stock?
     */
    public function isEntry(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * ¿Es una salida de stock?
     */
    public function isExit(): bool
    {
        return $this->quantity_change < 0;
    }

    /**
     * Obtener el tipo de movimiento en texto
     */
    public function getMovementTypeAttribute(): string
    {
        return $this->isEntry() ? 'Entrada' : 'Salida';
    }

    /**
     * Resumen de movimientos por período para una empresa
     */
    public static function getMovementsSummaryForCompany(
        User $company, 
        string $startDate, 
        string $endDate
    ): array {
        $movements = static::ofCompany($company)
            ->betweenDates($startDate, $endDate)
            ->selectRaw('
                reason,
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_entries,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as total_exits,
                COUNT(*) as total_movements
            ')
            ->groupBy('reason')
            ->get();

        return [
            'movements_by_reason' => $movements,
            'total_entries' => $movements->sum('total_entries'),
            'total_exits' => $movements->sum('total_exits'),
            'net_movement' => $movements->sum('total_entries') - $movements->sum('total_exits'),
        ];
    }

    // ================================
    // EVENTOS DEL MODELO
    // ================================

    protected static function booted(): void
    {
        // Asignar branch_id automáticamente si no se especifica
        static::creating(function (StockAdjustment $adjustment) {
            if (!$adjustment->branch_id && $adjustment->user) {
                // Si el usuario es sucursal (admin), usar su ID
                if ($adjustment->user->isAdmin()) {
                    $adjustment->branch_id = $adjustment->user->id;
                }
                // Si es empresa, usar la empresa
                elseif ($adjustment->user->isCompany()) {
                    $adjustment->branch_id = $adjustment->user->id;
                }
                // Si es usuario regular, usar su padre (sucursal)
                else {
                    $adjustment->branch_id = $adjustment->user->parent_id;
                }
            }
        });
    }
}

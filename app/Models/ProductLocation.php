<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductLocation
 * 
 * Maneja el stock de productos por ubicación/sucursal
 *
 * @property int $id
 * @property int $product_id
 * @property int $branch_id  (user_id con hierarchy_level = ADMIN)
 * @property decimal $stock
 * @property decimal $reserved_stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProductLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock',
        'reserved_stock',
    ];

    protected $casts = [
        'stock' => 'decimal:3',
        'reserved_stock' => 'decimal:3',
        'product_id' => 'integer',
        'branch_id' => 'integer',
    ];

    // ================================
    // RELACIONES
    // ================================

    /**
     * Producto al que pertenece este stock
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Sucursal donde está ubicado el stock
     * (Relación con User que tiene hierarchy_level = ADMIN y representa una Branch)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    /**
     * Obtener la entidad Branch a través del usuario que la representa
     */
    public function branchEntity(): ?Branch
    {
        return $this->branch?->representable_type === Branch::class 
            ? $this->branch->representable 
            : null;
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Solo ubicaciones con stock disponible
     */
    public function scopeWithStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Stock disponible (descontando reservado)
     */
    public function scopeWithAvailableStock($query)
    {
        return $query->whereRaw('(stock - reserved_stock) > 0');
    }

    /**
     * Filtrar por sucursal
     */
    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Filtrar por empresa (todas las sucursales de una company)
     */
    public function scopeInCompany($query, User $company)
    {
        $branchIds = $company->children()
            ->where('hierarchy_level', User::HIERARCHY_ADMIN)
            ->pluck('id')
            ->toArray();
            
        return $query->whereIn('branch_id', $branchIds);
    }

    // ================================
    // MÉTODOS DE CONVENIENCIA
    // ================================

    /**
     * Stock disponible (total - reservado)
     */
    public function getAvailableStockAttribute(): float
    {
        return max(0, $this->stock - $this->reserved_stock);
    }

    /**
     * ¿Tiene stock disponible?
     */
    public function hasAvailableStock(): bool
    {
        return $this->available_stock > 0;
    }

    /**
     * ¿Necesita restock basado en el min_stock del producto?
     */
    public function needsRestock(): bool
    {
        $minStock = $this->product->min_stock ?? 0;
        return $this->stock <= $minStock;
    }

    // ================================
    // MÉTODOS ESTÁTICOS DE CONSULTA
    // ================================

    /**
     * Obtener stock consolidado por producto
     */
    public static function getConsolidatedStock(array $productIds = null): array
    {
        $query = static::selectRaw('product_id, SUM(stock) as total_stock, COUNT(*) as locations_count')
            ->groupBy('product_id');
            
        if ($productIds) {
            $query->whereIn('product_id', $productIds);
        }
        
        return $query->get()->pluck('total_stock', 'product_id')->toArray();
    }

    /**
     * Obtener stock por sucursal para una empresa
     */
    public static function getStockByBranch(User $company, array $productIds = null): array
    {
        $branchIds = $company->children()
            ->where('hierarchy_level', User::HIERARCHY_ADMIN)
            ->pluck('id')
            ->toArray();

        if (empty($branchIds)) {
            return [];
        }

        $query = static::selectRaw('branch_id, product_id, stock')
            ->whereIn('branch_id', $branchIds);
            
        if ($productIds) {
            $query->whereIn('product_id', $productIds);
        }
        
        return $query->get()->groupBy('branch_id')->toArray();
    }

    /**
     * Crear o actualizar stock en ubicación
     */
    public static function updateStock(int $productId, int $branchId, float $stock): self
    {
        return static::updateOrCreate(
            [
                'product_id' => $productId,
                'branch_id' => $branchId,
            ],
            [
                'stock' => max(0, $stock), // No permitir stock negativo
            ]
        );
    }

    /**
     * Ajustar stock (incrementar/decrementar)
     */
    public static function adjustStock(int $productId, int $branchId, float $adjustment): self
    {
        $location = static::firstOrCreate([
            'product_id' => $productId,
            'branch_id' => $branchId,
        ], [
            'stock' => 0,
            'reserved_stock' => 0,
        ]);

        $newStock = max(0, $location->stock + $adjustment);
        $location->update(['stock' => $newStock]);

        return $location;
    }

    // ================================
    // BOOT
    // ================================

    protected static function booted(): void
    {
        // Validar que branch_id corresponda a un usuario Admin
        static::creating(function (ProductLocation $location) {
            $branch = User::find($location->branch_id);
            if (!$branch || !$branch->isAdmin()) {
                throw new \InvalidArgumentException("branch_id debe corresponder a un usuario con hierarchy_level ADMIN");
            }
            
            // Validaciones de negocio (reemplazando check constraints)
            if ($location->stock < 0) {
                throw new \InvalidArgumentException("Stock no puede ser negativo");
            }
            
            if ($location->reserved_stock < 0) {
                throw new \InvalidArgumentException("Stock reservado no puede ser negativo");
            }
            
            if ($location->reserved_stock > $location->stock) {
                throw new \InvalidArgumentException("Stock reservado no puede ser mayor al stock total");
            }
        });

        static::updating(function (ProductLocation $location) {
            if ($location->isDirty('branch_id')) {
                $branch = User::find($location->branch_id);
                if (!$branch || !$branch->isAdmin()) {
                    throw new \InvalidArgumentException("branch_id debe corresponder a un usuario con hierarchy_level ADMIN");
                }
            }
            
            // Validaciones de negocio en updates
            if ($location->stock < 0) {
                throw new \InvalidArgumentException("Stock no puede ser negativo");
            }
            
            if ($location->reserved_stock < 0) {
                throw new \InvalidArgumentException("Stock reservado no puede ser negativo");
            }
            
            if ($location->reserved_stock > $location->stock) {
                throw new \InvalidArgumentException("Stock reservado no puede ser mayor al stock total");
            }
        });
    }
}
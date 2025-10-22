<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use DomainException;

class Product extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'name',
        'sku',
        'price',
        'image',
        'stock',
        'is_active',
        'is_shared',
        'min_stock',
        'description',
        'category',
        'unit',
        'cost_price',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_shared'   => 'boolean',
        'price'       => 'decimal:2',
        'cost_price'  => 'decimal:2',
        // Si stock es entero: 'stock' => 'integer'. Si es fraccional (kg): decimal.
        'stock'       => 'decimal:3', 
        'min_stock'   => 'decimal:3',
        'user_id'     => 'integer',
        'company_id'  => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_shared' => false,
        'stock' => 0,
        'min_stock' => 0,
    ];

    protected $appends = ['is_low_stock'];

    // ---------- Relaciones ----------
    public function user() { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(User::class, 'company_id'); }
    public function adjustments() { return $this->hasMany(StockAdjustment::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function recipeItems() { return $this->hasMany(ProductRecipe::class); }

    // ---------- Scopes ----------
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Productos disponibles para un usuario según jerarquía.
     * - Master: todos
     * - Company: company_id = user.id
     * - Admin (branch): sus productos OR productos compartidos de la company
     * - Usuario normal: productos de su branch (user_id == parent_id) y compartidos
     */
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
                  ->orWhere(function ($sq) use ($company) {
                      $sq->where('company_id', $company->id)
                         ->where('is_shared', true);
                  });
            });
        }

        // usuario regular
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($sq) use ($user) {
                  if ($user->parent_id) {
                      $sq->where('user_id', $user->parent_id)
                         ->where('is_shared', true);
                  } else {
                      // Si no hay parent, no traer shared from parent
                      $sq->whereRaw('1 = 0');
                  }
              });
        });
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeShared(Builder $query): Builder
    {
        return $query->where('is_shared', true);
    }

    // ---------- Accesores ----------
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    // ---------- Business logic ----------
    /**
     * ¿Puede este usuario editar el producto? (lógica rápida; usar Policy en controllers)
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->isMaster()) return true;
        if ($this->user_id === $user->id) return true;
        if ($user->isCompany() && $this->company_id === $user->id) return true;
        return false;
    }

    public function isAvailableForSale(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    public function needsRestock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Ajusta stock de forma ATÓMICA y registra el ajuste.
     *
     * - quantityChange puede ser positivo (ingreso) o negativo (venta).
     * - Devuelve la instancia StockAdjustment creada.
     * - Lanza DomainException si no se puede completar (ej: stock insuficiente).
     */
    public function adjustStock(float $quantityChange, string $reason, ?User $actor = null, $reference = null): StockAdjustment
    {
        return DB::transaction(function () use ($quantityChange, $reason, $actor, $reference) {
            // Bloquea la fila para evitar race conditions
            $product = static::where('id', $this->id)->lockForUpdate()->first();

            if (!$product) {
                throw new DomainException("Producto no encontrado (id {$this->id}).");
            }

            $oldStock = (float) $product->stock;
            $newStock = $oldStock + $quantityChange;

            // Validaciones de negocio: evitar stock negativo salvo que permitas backorders
            if ($newStock < 0) {
                throw new DomainException("Stock insuficiente para SKU {$product->sku} (actual {$oldStock}, cambio {$quantityChange}).");
            }

            // Actualización segura usando Eloquent (ya bloqueado)
            $product->stock = $newStock;
            $product->save();

            // Registrar ajuste
            $adjustment = StockAdjustment::create([
                'user_id' => $actor?->id ?? auth()->id(),
                'product_id' => $product->id,
                'quantity_change' => $quantityChange,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
            ]);

            // Opcional: dispatch event StockAdjusted / Log
            // event(new \App\Events\StockAdjusted($adjustment));

            return $adjustment;
        }, 5);
    }

    /**
     * Consolidated stock por company. Si querés sumar solo productos compartidos:
     * ->where('is_shared', true)
     */
    public static function getConsolidatedStockForCompany(User $company)
    {
        return static::selectRaw('sku, name, SUM(stock) as total_stock, MIN(min_stock) as min_stock_required, COUNT(*) as locations_count')
            ->where('company_id', $company->id)
            ->active()
            ->groupBy(['sku', 'name'])
            ->get();
    }

    // ---------- Boot ----------
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (!$product->company_id && $product->user) {
                $product->company_id = $product->user->rootCompany()?->id ?? $product->user_id;
            }
        });

        static::created(function (Product $product) {
            // Registrar ajuste inicial si hay stock > 0
            if ($product->stock > 0) {
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'quantity_change' => $product->stock,
                    'old_stock' => 0,
                    'new_stock' => $product->stock,
                    'reason' => 'producto_creado',
                    'reference_id' => null,
                    'reference_type' => null,
                    'user_id' => $product->user_id,
                ]);
            }
        });
    }
    public function branch()
{
    return $this->belongsTo(User::class, 'branch_id');
}
public function productLocations()
{
    return $this->hasMany(ProductLocation::class);
}

}

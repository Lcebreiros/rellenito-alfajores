<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
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
        'barcode',
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
        'created_by_type',
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
        // Asegura que un usuario "company" pueda ver también productos de sus sucursales
        // (de lo contrario, el scope global byUser limitaría a user_id = auth()->id)
        $query = $query->withoutGlobalScope('byUser');
        if ($user->isMaster()) {
            return $query;
        }

        if ($user->isCompany()) {
            return $query->where('company_id', $user->id);
        }

        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            $branch = $user->branch();
            if ($branch && (bool)($branch->use_company_inventory ?? false)) {
                // Inventario compartido: usar catálogo completo de la empresa
                return $query->where('company_id', $company->id);
            }
            // Inventario propio: sus productos + compartidos de la empresa
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
                      // Si el parent es una sucursal que usa inventario de empresa, exponer catálogo de empresa completo
                      $parent = $user->parent; // relación
                      $branch = $parent?->branch();
                      $company = $user->rootCompany();
                      if ($branch && (bool)($branch->use_company_inventory ?? false)) {
                          $sq->where('company_id', $company?->id ?? 0);
                      } else {
                          $sq->where('user_id', $user->parent_id)
                             ->where('is_shared', true);
                      }
                  } else {
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

    // ---------- Route binding (permite que Company vea productos de sus sucursales) ----------
    public function resolveRouteBinding($value, $field = null)
    {
        $query = static::query()->withoutGlobalScope('byUser');
        if ($field) {
            $query->where($field, $value);
        } else {
            $query->where($this->getRouteKeyName(), $value);
        }

        $product = $query->firstOrFail();

        $user = auth()->user();
        if (!$user) {
            abort(404);
        }

        // Master ve todo
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return $product;
        }

        // Company: puede ver productos donde company_id = company.id (incluye sucursales)
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            if ((int)($product->company_id) === (int)$user->id) {
                return $product;
            }
            abort(404);
        }

        // Si la sucursal (o el usuario cuya parent es la sucursal) usa inventario de empresa,
        // permitir acceso al catálogo completo de la empresa (sin requerir is_shared)
        try {
            $company = method_exists($user, 'rootCompany') ? $user->rootCompany() : null;
            $branch  = method_exists($user, 'branch') ? $user->branch() : null;

            // Caso: usuario admin (branch user)
            if ($branch && (bool)($branch->use_company_inventory ?? false)) {
                if ($company && (int)$product->company_id === (int)$company->id) {
                    return $product;
                }
            }

            // Caso: usuario regular cuyo parent es la sucursal
            if (!$branch && $user->parent_id) {
                $parent = $user->parent; // User (branch user)
                $parentBranch = method_exists($parent, 'branch') ? $parent->branch() : null;
                if ($parentBranch && (bool)($parentBranch->use_company_inventory ?? false)) {
                    if ($company && (int)$product->company_id === (int)$company->id) {
                        return $product;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Si algo falla en la detección, seguimos con las reglas clásicas de abajo
        }

        // Admin/usuario: dueños o compartidos del parent
        if ((int)$product->user_id === (int)$user->id) {
            return $product;
        }

        if ($user->parent_id) {
            if ((int)$product->user_id === (int)$user->parent_id && (bool)$product->is_shared) {
                return $product;
            }
        }

        abort(404);
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
        $user = auth()->user();
        
        // Detectar quién crea: Company (nivel 0) o Branch (nivel 1)
        if ($user) {
            if ($user->hierarchy_level === User::HIERARCHY_ADMIN) {
                // Admin de sucursal
                $product->created_by_type = 'branch';
            } else {
                // Company o Master
                $product->created_by_type = 'company';
            }
        }
        
        // Asignar company_id
        if (!$product->company_id && $product->user) {
            $product->company_id = $product->user->rootCompany()?->id ?? $product->user_id;
        }
    });

    static::created(function (Product $product) {
        // Registrar ajuste inicial solo si hay stock > 0
        if ($product->stock > 0) {
            StockAdjustment::create([
                'product_id' => $product->id,
                'quantity_change' => $product->stock,
                'new_stock' => $product->stock,
                'reason' => 'producto_creado',
                'reference_id' => null,
                'reference_type' => null,
                'user_id' => $product->user_id,
            ]);

            // Si el creador es una sucursal (usuario admin), crear ubicación con el stock inicial
            try {
                $owner = \App\Models\User::find($product->user_id);
                if ($owner && method_exists($owner, 'isAdmin') && $owner->isAdmin()) {
                    \App\Models\ProductLocation::updateStock((int)$product->id, (int)$owner->id, (float)$product->stock);
                }
            } catch (\Throwable $e) {
                // No bloquear si falla la creación de ubicación
            }
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

public function scopeCreatedByCompany(Builder $query): Builder
{
    return $query->where('created_by_type', 'company');
}

public function scopeCreatedByBranch(Builder $query): Builder
{
    return $query->where('created_by_type', 'branch');
}

}

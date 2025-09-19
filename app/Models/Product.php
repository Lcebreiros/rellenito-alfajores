<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;
use App\Models\StockAdjustment;

class Product extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'name', 'sku', 'price', 'stock', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'price'     => 'decimal:2',
    ];

    // Relaciones
    public function user()        { return $this->belongsTo(User::class); }
    public function orderItems()  { return $this->hasMany(OrderItem::class); }
    public function adjustments() { return $this->hasMany(StockAdjustment::class); }
    public function recipeItems() { return $this->hasMany(ProductRecipe::class); }

    // Scopes
    public function scopeActive($query) { return $query->where('is_active', true); }

    // 🔹 Booted: opcional, solo log inicial de creación si no afecta transacciones
    protected static function booted()
{
    static::created(function ($product) {
        if ($product->stock > 0) {
            StockAdjustment::create([
                'product_id'      => $product->id,
                'quantity_change' => $product->stock,
                'new_stock'       => $product->stock,
                'reason'          => 'creado',
                'reference_id'    => null,
                'reference_type'  => null,
                'user_id'         => $product->user_id,
            ]);
        }
    });
}

}

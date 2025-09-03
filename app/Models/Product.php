<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Product extends Model
{
    use BelongsToUser; // ← scope global por usuario + autocompleta user_id al crear

    protected $fillable = [
        'user_id', // opcional si confiás en el trait; útil para create() masivo
        'name','sku','price','stock','is_active',
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
    // (opcional) public function costings() { return $this->hasMany(Costing::class); }

    // Scopes
    public function scopeActive($q){ return $q->where('is_active', true); }
}

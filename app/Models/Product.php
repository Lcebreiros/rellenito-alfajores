<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','sku','price','stock','is_active'];
    protected $casts = ['is_active'=>'bool','price'=>'decimal:2'];

    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function adjustments() { return $this->hasMany(StockAdjustment::class); }

    public function scopeActive($q){ return $q->where('is_active', true); }
}
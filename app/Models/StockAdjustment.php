<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['product_id','quantity_change','reason','reference_id','reference_type'];

    public function product (){ return $this->belongsTo(Product::class); }
    public function reference (){ return $this->morphTo(); }
}

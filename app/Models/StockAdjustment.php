<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class StockAdjustment extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id','product_id','quantity_change','reason','reference_id','reference_type'];

    public function product (){ return $this->belongsTo(Product::class); }
    public function reference (){ return $this->morphTo(); }
}

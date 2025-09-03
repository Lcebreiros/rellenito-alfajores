<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class OrderItem extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id','order_id', 'product_id','quantity','unit_price','subtotal'];
    protected $casts = ['unit_price'=>'decimal:2','subtotal'=>'decimal:2'];

    public function order(){ return $this->belongsTo(Order::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}

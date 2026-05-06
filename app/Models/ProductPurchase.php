<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPurchase extends Model
{
    protected $fillable = ['user_id', 'product_id', 'qty', 'unit', 'total_cost', 'purchased_at'];

    protected $casts = [
        'purchased_at' => 'date',
        'qty'          => 'decimal:3',
        'total_cost'   => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

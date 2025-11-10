<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRecipe extends Model
{

    protected $fillable = [
        'product_id', 'supply_id', 'qty', 'unit', 'waste_pct',
    ];

    public function product(): BelongsTo
    {
        // Relación estándar; la FK compuesta la hace cumplir la DB
        return $this->belongsTo(Product::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
}

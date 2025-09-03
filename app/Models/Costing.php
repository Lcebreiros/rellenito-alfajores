<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Costing extends Model
{

    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'source',       // 'recipe' | 'quick' (si alguna vez usas alta rápida)
        'yield_units',
        'unit_total',
        'batch_total',
        'lines',        // JSON: [{id,name,base_unit,per_unit_qty,per_unit_cost,perc}, ...]
        'product_id',
        'product_name', // denormalizado para mostrar rápido
    ];

    protected $casts = [
        'yield_units' => 'integer',
        'unit_total'  => 'decimal:2',
        'batch_total' => 'decimal:2',
        'lines'       => 'array',
    ];

    public function product(){ return $this->belongsTo(Product::class); }
}

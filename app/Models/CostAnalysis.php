<?php

// app/Models/CostAnalysis.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostAnalysis extends Model
{
    protected $fillable = [
        'product_id','source','yield_units','unit_total','batch_total','lines'
    ];
    protected $casts = [
        'lines' => 'array',
        'yield_units' => 'integer',
        'unit_total' => 'float',
        'batch_total' => 'float',
    ];

    public function product(){ return $this->belongsTo(Product::class); }
}

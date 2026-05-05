<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class SupplyPurchase extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'supply_id',
        'qty',
        'unit',
        'unit_to_base',
        'total_cost',
        'purchased_at',
    ];

    protected $casts = [
        'qty'          => 'decimal:4',
        'unit_to_base' => 'decimal:4',
        'total_cost'   => 'decimal:2',
        'purchased_at' => 'date',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}

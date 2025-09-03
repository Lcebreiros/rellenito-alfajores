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
        'qty',           // cantidad cargada (en la unidad elegida)
        'unit',          // 'g','kg','ml','l','cm3','u'
        'unit_to_base',  // factor hacia la unidad base del supply (kg->g = 1000, l->ml = 1000, u->1)
        'total_cost',    // $ total pagado por esta compra
    ];

    protected $casts = [
        'qty'          => 'decimal:4',
        'unit_to_base' => 'decimal:4',
        'total_cost'   => 'decimal:2',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}

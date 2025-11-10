<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSupply extends Model
{
    protected $fillable = [
        'service_id',
        'supply_id',
        'qty',
        'unit',
        'waste_pct',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'waste_pct' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
}

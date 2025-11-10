<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyService extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_name',
        'provider_name',
        'description',
        'cost',
        'frequency',
        'next_payment_date',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'next_payment_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula el costo anualizado según la frecuencia
     */
    public function getAnnualizedCostAttribute(): float
    {
        $multipliers = [
            'unica' => 1,
            'diaria' => 365,
            'semanal' => 52,
            'mensual' => 12,
            'anual' => 1,
        ];

        return $this->cost * ($multipliers[$this->frequency] ?? 1);
    }
}

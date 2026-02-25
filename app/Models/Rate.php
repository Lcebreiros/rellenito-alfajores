<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'rental_type',
        'name',
        'vehicle_type',
        'fraction_minutes',
        'price_per_fraction',
        'initial_block_minutes',
        'initial_block_price',
        'hour_price',
        'half_day_price',
        'day_price',
        'week_price',
        'month_price',
        'is_active',
    ];

    protected $casts = [
        'fraction_minutes' => 'integer',
        'price_per_fraction' => 'decimal:2',
        'initial_block_minutes' => 'integer',
        'initial_block_price' => 'decimal:2',
        'hour_price' => 'decimal:2',
        'half_day_price' => 'decimal:2',
        'day_price' => 'decimal:2',
        'week_price' => 'decimal:2',
        'month_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'rental_type' => 'parking',
        'is_active' => true,
    ];

    /**
     * Estadías de parking que usan esta tarifa
     */
    public function parkingStays(): HasMany
    {
        return $this->hasMany(ParkingStay::class, 'rate_id');
    }

    /**
     * Scope para filtrar por tipo de alquiler
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('rental_type', $type);
    }

    /**
     * Scope para tarifas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para parking (retrocompatibilidad)
     */
    public function scopeParking($query)
    {
        return $query->where('rental_type', 'parking');
    }
}

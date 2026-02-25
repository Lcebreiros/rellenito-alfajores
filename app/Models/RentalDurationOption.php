<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class RentalDurationOption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rental_space_id',
        'label',
        'minutes',
        'price',
        'is_active',
    ];

    protected $casts = [
        'minutes' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(RentalSpace::class, 'rental_space_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

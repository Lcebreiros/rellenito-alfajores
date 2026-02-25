<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class RentalSpace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'description',
        'color',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RentalSpaceCategory::class, 'category_id');
    }

    public function durationOptions(): HasMany
    {
        return $this->hasMany(RentalDurationOption::class, 'rental_space_id');
    }

    public function activeDurationOptions(): HasMany
    {
        return $this->hasMany(RentalDurationOption::class, 'rental_space_id')
            ->where('is_active', true)
            ->orderBy('minutes');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'rental_space_id');
    }

    public function scopeForCompany(Builder $query, User $user): Builder
    {
        $companyId = $user->isCompany() ? $user->id : $user->parent_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

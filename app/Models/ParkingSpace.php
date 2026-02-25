<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpace extends Model
{
    use SoftDeletes;

    public const STATUS_AVAILABLE = 'disponible';
    public const STATUS_BUSY = 'ocupada';
    public const STATUS_RENTED = 'alquilada';
    public const STATUS_MAINTENANCE = 'mantenimiento';
    public const USAGE_HOURLY = 'horaria';
    public const USAGE_MONTHLY = 'mensual';

    protected $fillable = [
        'company_id',
        'category_id',
        'rate_id',
        'service_id',
        'name',
        'code',
        'status',
        'usage',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ParkingSpaceCategory::class, 'category_id');
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class, 'rate_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(ParkingStay::class, 'parking_space_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $fillable = [
        'company_id',
        'parking_shift_id',
        'cash_session_id',
        'order_id',
        'created_by',
        'type',
        'amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function parkingShift(): BelongsTo
    {
        return $this->belongsTo(ParkingShift::class, 'parking_shift_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIngresos($query)
    {
        return $query->whereIn('type', ['ingreso', 'apertura']);
    }

    public function scopeEgresos($query)
    {
        return $query->where('type', 'egreso');
    }

    public function scopeVentas($query)
    {
        return $query->where('type', 'sale');
    }

    public function isIngreso(): bool  { return $this->type === 'ingreso'; }
    public function isEgreso(): bool   { return $this->type === 'egreso'; }
    public function isSale(): bool     { return $this->type === 'sale'; }
    public function isApertura(): bool { return $this->type === 'apertura'; }

    public function isPositive(): bool
    {
        return in_array($this->type, ['ingreso', 'sale', 'apertura']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $fillable = [
        'company_id',
        'parking_shift_id',
        'created_by',
        'type',
        'amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relación con la company (usuario padre)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Relación con el turno de parking
     */
    public function parkingShift(): BelongsTo
    {
        return $this->belongsTo(ParkingShift::class, 'parking_shift_id');
    }

    /**
     * Relación con el usuario que creó el movimiento
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para ingresos
     */
    public function scopeIngresos($query)
    {
        return $query->where('type', 'ingreso');
    }

    /**
     * Scope para egresos
     */
    public function scopeEgresos($query)
    {
        return $query->where('type', 'egreso');
    }

    /**
     * Helper para verificar si es ingreso
     */
    public function isIngreso(): bool
    {
        return $this->type === 'ingreso';
    }

    /**
     * Helper para verificar si es egreso
     */
    public function isEgreso(): bool
    {
        return $this->type === 'egreso';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'opening_amount',
        'closing_amount',
        'closing_note',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'opened_at'      => 'datetime',
        'closed_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** Saldo actual = apertura + ingresos + ventas - egresos */
    public function currentBalance(): float
    {
        $ingresos = (float) $this->movements()
            ->whereIn('type', ['ingreso', 'sale', 'apertura'])
            ->sum('amount');

        $egresos = (float) $this->movements()
            ->where('type', 'egreso')
            ->sum('amount');

        return round($ingresos - $egresos, 2);
    }

    /** Total de ventas en la sesión */
    public function salesTotal(): float
    {
        return round((float) $this->movements()->where('type', 'sale')->sum('amount'), 2);
    }

    /** Cantidad de ventas en la sesión */
    public function salesCount(): int
    {
        return $this->movements()->where('type', 'sale')->count();
    }

    /** Sesión abierta activa de un usuario */
    public static function activeFor(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('status', 'open')
            ->latest()
            ->first();
    }
}

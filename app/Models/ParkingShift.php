<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingShift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'previous_shift_id',
        'operator_name',
        'status',
        'started_at',
        'ended_at',
        'initial_cash',
        'incomes_total',
        'cash_counted',
        'expected_cash',
        'cash_difference',
        'envelope_amount',
        'remaining_cash',
        'mp_amount',
        'total_discounts',
        'total_movements',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'initial_cash' => 'decimal:2',
        'incomes_total' => 'decimal:2',
        'cash_counted' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'envelope_amount' => 'decimal:2',
        'remaining_cash' => 'decimal:2',
        'mp_amount' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_movements' => 'integer',
    ];

    // Relaciones

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function previousShift(): BelongsTo
    {
        return $this->belongsTo(ParkingShift::class, 'previous_shift_id');
    }

    public function nextShift(): HasMany
    {
        return $this->hasMany(ParkingShift::class, 'previous_shift_id');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(ParkingStay::class, 'parking_shift_id');
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'parking_shift_id');
    }

    // Métodos de cálculo automático

    /**
     * Calcula y actualiza todos los totales del turno
     */
    public function recalculateTotals(): void
    {
        $stays = $this->stays()->where('status', 'closed')->with('paymentMethods')->get();

        // Total de movimientos (solo completos: ingreso + egreso)
        $this->total_movements = $stays->count();

        // Total de ingresos
        $this->incomes_total = $stays->sum('total_amount');

        // Total de descuentos
        $this->total_discounts = $stays->sum('discount_amount');

        // Totales por método de pago
        $mpTotal = 0;
        foreach ($stays as $stay) {
            $hasMercadoPago = $stay->paymentMethods->contains(function ($pm) {
                return in_array(strtolower($pm->name), ['mercadopago', 'mercado pago', 'mp'])
                    || (isset($pm->slug) && $pm->slug === 'mercadopago');
            });

            if ($hasMercadoPago) {
                $mpTotal += $stay->total_amount;
            }
        }
        $this->mp_amount = $mpTotal;

        // Efectivo esperado = inicial + cobros en efectivo + ingresos de caja - egresos de caja
        $cashPayments = $this->incomes_total - $this->mp_amount;

        // Sumar ingresos y restar egresos de caja
        $cashIngresos = $this->cashMovements()->where('type', 'ingreso')->sum('amount');
        $cashEgresos = $this->cashMovements()->where('type', 'egreso')->sum('amount');

        $this->expected_cash = $this->initial_cash + $cashPayments + $cashIngresos - $cashEgresos;

        $this->save();
    }

    /**
     * Calcula la diferencia de caja al cierre
     */
    public function calculateCashDifference(): float
    {
        return $this->cash_counted - $this->expected_cash;
    }

    /**
     * Verifica si el turno está abierto
     */
    public function isOpen(): bool
    {
        return $this->status === 'open' && is_null($this->ended_at);
    }

    /**
     * Verifica si el turno está cerrado
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed' && !is_null($this->ended_at);
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')->whereNull('ended_at');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed')->whereNotNull('ended_at');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}

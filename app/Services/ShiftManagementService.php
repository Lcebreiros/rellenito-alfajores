<?php

namespace App\Services;

use App\Models\ParkingShift;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Servicio para gestión de turnos de operarios de parking
 */
class ShiftManagementService
{
    /**
     * Abre un nuevo turno para un empleado
     *
     * @param Employee $employee
     * @param User $company
     * @param float $initialCash
     * @param string|null $operatorName Nombre del operador (opcional, si no se proporciona se usa el nombre del empleado)
     * @return ParkingShift
     * @throws ValidationException
     */
    public function openShift(Employee $employee, User $company, float $initialCash, ?string $operatorName = null): ParkingShift
    {
        // Verificar que no haya un turno abierto para esta empresa
        $existingOpenShift = ParkingShift::where('company_id', $company->id)
            ->where('status', 'open')
            ->whereNull('ended_at')
            ->first();

        if ($existingOpenShift) {
            throw ValidationException::withMessages([
                'shift' => 'Ya hay un turno abierto. Solo puede haber un turno activo a la vez.'
            ]);
        }

        // Buscar el último turno cerrado para obtener el efectivo restante
        $previousShift = $this->findPreviousShift($company->id);

        // Usar el nombre proporcionado o el nombre del empleado
        $finalOperatorName = $operatorName ?? $employee->name;

        return DB::transaction(function () use ($employee, $company, $initialCash, $previousShift, $finalOperatorName) {
            $shift = ParkingShift::create([
                'company_id' => $company->id,
                'employee_id' => $employee->id,
                'operator_name' => $finalOperatorName,
                'previous_shift_id' => $previousShift?->id,
                'status' => 'open',
                'started_at' => now(),
                'initial_cash' => $initialCash,
                'expected_cash' => $initialCash,
                'incomes_total' => 0,
                'cash_counted' => 0,
                'cash_difference' => 0,
                'envelope_amount' => 0,
                'remaining_cash' => 0,
                'mp_amount' => 0,
                'total_discounts' => 0,
                'total_movements' => 0,
            ]);

            return $shift;
        });
    }

    /**
     * Cierra un turno y realiza el arqueo de caja
     *
     * @param ParkingShift $shift
     * @param float $actualCash - Efectivo realmente contado por el operario
     * @param float $envelopeAmount - Efectivo que va al buzón/caja fuerte
     * @param string|null $notes - Notas del cierre
     * @return ParkingShift
     * @throws ValidationException
     */
    public function closeShift(
        ParkingShift $shift,
        float $actualCash,
        float $envelopeAmount = 0,
        ?string $notes = null
    ): ParkingShift {
        if ($shift->isClosed()) {
            throw ValidationException::withMessages([
                'shift' => 'Este turno ya está cerrado.'
            ]);
        }

        return DB::transaction(function () use ($shift, $actualCash, $envelopeAmount, $notes) {
            // Recalcular todos los totales antes de cerrar
            $shift->recalculateTotals();

            // Actualizar datos del cierre
            $shift->cash_counted = $actualCash;
            $shift->envelope_amount = $envelopeAmount;

            // Calcular diferencia de caja
            $shift->cash_difference = $shift->calculateCashDifference();

            // Calcular efectivo que queda para el próximo turno
            $shift->remaining_cash = $actualCash - $envelopeAmount;

            // Cerrar el turno
            $shift->ended_at = now();
            $shift->status = 'closed';

            if ($notes) {
                $shift->notes = $notes;
            }

            $shift->save();

            return $shift;
        });
    }

    /**
     * Encuentra el turno anterior más reciente para una empresa
     *
     * @param int $companyId
     * @return ParkingShift|null
     */
    public function findPreviousShift(int $companyId): ?ParkingShift
    {
        return ParkingShift::where('company_id', $companyId)
            ->where('status', 'closed')
            ->whereNotNull('ended_at')
            ->latest('ended_at')
            ->first();
    }

    /**
     * Obtiene el turno abierto actual para una empresa
     *
     * @param int $companyId
     * @return ParkingShift|null
     */
    public function getCurrentOpenShift(int $companyId): ?ParkingShift
    {
        return ParkingShift::where('company_id', $companyId)
            ->where('status', 'open')
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * Verifica si hay un turno abierto
     *
     * @param int $companyId
     * @return bool
     */
    public function hasOpenShift(int $companyId): bool
    {
        return $this->getCurrentOpenShift($companyId) !== null;
    }

    /**
     * Obtiene estadísticas del turno
     *
     * @param ParkingShift $shift
     * @return array
     */
    public function getShiftStatistics(ParkingShift $shift): array
    {
        $shift->recalculateTotals();

        $stays = $shift->stays()
            ->where('status', 'closed')
            ->with(['paymentMethods', 'discount'])
            ->get();

        // Contar autos actuales (estadías abiertas/sin egreso)
        $currentCars = $shift->stays()
            ->where('status', 'open')
            ->count();

        // Calcular totales por método de pago
        $cashTotal = 0;
        $mpTotal = 0;

        foreach ($stays as $stay) {
            $paymentMethods = $stay->paymentMethods;

            if ($paymentMethods->isEmpty()) {
                // Si no tiene métodos de pago especificados, asumir efectivo
                $cashTotal += $stay->total_amount;
            } else {
                foreach ($paymentMethods as $pm) {
                    if ($pm->name === 'mercadopago' || $pm->slug === 'mercadopago') {
                        $mpTotal += $pm->pivot->amount ?? $stay->total_amount;
                    } else {
                        $cashTotal += $pm->pivot->amount ?? $stay->total_amount;
                    }
                }
            }
        }

        // Obtener movimientos de caja
        $cashMovements = $shift->cashMovements()->orderBy('created_at')->get();
        $cashIngresos = $cashMovements->where('type', 'ingreso')->sum('amount');
        $cashEgresos = $cashMovements->where('type', 'egreso')->sum('amount');

        // Calcular efectivo esperado en caja (inicial + cobros en efectivo + ingresos - egresos)
        $expectedCash = $shift->initial_cash + $cashTotal + $cashIngresos - $cashEgresos;

        return [
            'total_movements' => $shift->total_movements,
            'current_cars' => $currentCars,
            'total_income' => $shift->incomes_total,
            'cash_payments' => $cashTotal, // Solo cobros en efectivo (sin inicial)
            'total_cash_in_box' => $expectedCash, // Efectivo total en caja (inicial + cobros + ingresos - egresos)
            'mp_payments' => $mpTotal,
            'total_discounts' => $shift->total_discounts,
            'initial_cash' => $shift->initial_cash,
            'expected_cash' => $expectedCash,
            'actual_cash' => $shift->cash_counted,
            'cash_difference' => $shift->cash_difference,
            'envelope_amount' => $shift->envelope_amount,
            'remaining_cash' => $shift->remaining_cash,
            'duration_hours' => $shift->started_at->diffInHours($shift->ended_at ?? now()),
            'previous_shift_remaining' => $shift->previousShift?->remaining_cash ?? 0,
            'cash_ingresos' => $cashIngresos,
            'cash_egresos' => $cashEgresos,
            'cash_movements_count' => $cashMovements->count(),
            'cash_movements' => $cashMovements->map(function ($movement) {
                return [
                    'type' => $movement->type,
                    'amount' => $movement->amount,
                    'description' => $movement->description,
                    'notes' => $movement->notes,
                    'created_at' => $movement->created_at,
                ];
            }),
        ];
    }

    /**
     * Obtiene el historial de turnos de un empleado
     *
     * @param Employee $employee
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmployeeShiftHistory(Employee $employee, int $limit = 20)
    {
        return ParkingShift::where('employee_id', $employee->id)
            ->where('status', 'closed')
            ->with(['company', 'previousShift'])
            ->orderBy('ended_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Genera reporte de cierre de turno
     *
     * @param ParkingShift $shift
     * @return array
     */
    public function generateShiftReport(ParkingShift $shift): array
    {
        $statistics = $this->getShiftStatistics($shift);

        $stays = $shift->stays()
            ->where('status', 'closed')
            ->with(['paymentMethods', 'discount', 'rate'])
            ->orderBy('exit_at', 'desc')
            ->get();

        return [
            'shift' => $shift,
            'statistics' => $statistics,
            'movements' => $stays->map(function ($stay) {
                return [
                    'id' => $stay->id,
                    'license_plate' => $stay->license_plate,
                    'vehicle_type' => $stay->vehicle_type,
                    'entry_at' => $stay->entry_at,
                    'exit_at' => $stay->exit_at,
                    'duration' => $stay->entry_at->diff($stay->exit_at)->format('%H:%I'),
                    'total_amount' => $stay->total_amount,
                    'discount_amount' => $stay->discount_amount,
                    'discount_name' => $stay->discount?->name,
                    'payment_methods' => $stay->paymentMethods->pluck('name')->join(', '),
                ];
            }),
        ];
    }
}

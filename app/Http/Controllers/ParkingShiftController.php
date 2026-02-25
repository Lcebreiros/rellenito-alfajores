<?php

namespace App\Http\Controllers;

use App\Models\ParkingShift;
use App\Models\Employee;
use App\Services\ShiftManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParkingShiftController extends Controller
{
    /**
     * Muestra el historial general de entradas y salidas por día
     * Accesible para operarios y superiores
     */
    public function myHistory(Request $request)
    {
        $user = Auth::user();
        $isMaster = $user->isMaster();
        $companyId = $user->isCompany() ? $user->id : $user->parent_id;

        // Fecha por defecto: últimos 30 días
        $fromDate = $request->input('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        // Query base para obtener estadías cerradas
        if ($isMaster) {
            $staysQuery = \App\Models\ParkingStay::where('status', 'closed')
                ->whereDate('exit_at', '>=', $fromDate)
                ->whereDate('exit_at', '<=', $toDate);
        } else {
            $staysQuery = \App\Models\ParkingStay::where('company_id', $companyId)
                ->where('status', 'closed')
                ->whereDate('exit_at', '>=', $fromDate)
                ->whereDate('exit_at', '<=', $toDate);
        }

        // Manejar descarga de reporte
        if ($request->has('download')) {
            return $this->downloadHistoryReport($staysQuery, $fromDate, $toDate, $request->input('format', 'excel'));
        }

        // Obtener todos los movimientos con sus relaciones
        $movements = $staysQuery
            ->with(['parkingSpace', 'discount', 'paymentMethods'])
            ->orderBy('exit_at', 'desc')
            ->get();

        // Agrupar movimientos por fecha
        $movementsByDate = $movements->groupBy(function($stay) {
            return $stay->exit_at->format('Y-m-d');
        });

        // Calcular estadísticas generales
        $totalStats = [
            'total_entries' => $movements->count(),
            'total_income' => $movements->sum('total_amount'),
            'total_discounts' => $movements->sum('discount_amount'),
            'days_count' => $movementsByDate->count(),
        ];

        return view('parking.shifts.my-history', compact('movementsByDate', 'totalStats', 'fromDate', 'toDate', 'isMaster'));
    }

    /**
     * Descargar reporte de historial
     */
    private function downloadHistoryReport($query, $fromDate, $toDate, $format)
    {
        $movements = $query
            ->with(['parkingSpace', 'discount', 'paymentMethods'])
            ->orderBy('exit_at', 'desc')
            ->get();

        if ($format === 'excel') {
            return $this->downloadExcel($movements, $fromDate, $toDate);
        } else {
            return $this->downloadPdf($movements, $fromDate, $toDate);
        }
    }

    /**
     * Descargar reporte en Excel (CSV)
     */
    private function downloadExcel($movements, $fromDate, $toDate)
    {
        $filename = "historial_movimientos_{$fromDate}_{$toDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($file, [
                'Fecha',
                'Patente',
                'Tipo Vehículo',
                'Entrada',
                'Salida',
                'Duración',
                'Cochera',
                'Total',
                'Descuento',
                'Monto Final',
                'Método de Pago'
            ]);

            // Datos
            foreach ($movements as $movement) {
                $duration = $movement->entry_at->diff($movement->exit_at);
                $durationFormatted = sprintf('%dh %dm', $duration->h + ($duration->days * 24), $duration->i);

                $paymentMethods = $movement->paymentMethods->map(function($pm) {
                    return $pm->payment_method . ': $' . number_format($pm->amount, 2);
                })->implode(', ');

                fputcsv($file, [
                    $movement->exit_at->format('d/m/Y'),
                    $movement->license_plate,
                    $movement->vehicle_type ?? 'Auto',
                    $movement->entry_at->format('d/m/Y H:i'),
                    $movement->exit_at->format('d/m/Y H:i'),
                    $durationFormatted,
                    $movement->parkingSpace->name ?? '-',
                    number_format($movement->total_amount, 2, ',', '.'),
                    number_format($movement->discount_amount, 2, ',', '.'),
                    number_format($movement->total_amount - $movement->discount_amount, 2, ',', '.'),
                    $paymentMethods ?: 'Efectivo'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Descargar reporte en PDF
     */
    private function downloadPdf($movements, $fromDate, $toDate)
    {
        // Para implementación futura con DOMPDF o similar
        abort(501, 'Descarga en PDF no implementada aún');
    }

    /**
     * Panel de auditoría para encargados/empresa
     * Muestra todos los turnos con filtros
     */
    public function audit(Request $request)
    {
        $user = Auth::user();
        $isMaster = $user->isMaster();
        $companyId = $user->isCompany() ? $user->id : $user->parent_id;

        // Query base
        if ($isMaster) {
            // Master ve todos los turnos
            $query = ParkingShift::with(['employee', 'previousShift'])
                ->orderBy('started_at', 'desc');

            $employees = Employee::orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $totalShifts = ParkingShift::count();
            $totalIncome = ParkingShift::where('status', 'closed')->sum('incomes_total');
            $totalMovements = ParkingShift::where('status', 'closed')->sum('total_movements');
        } else {
            // Company ve solo sus turnos
            $query = ParkingShift::where('company_id', $companyId)
                ->with(['employee', 'previousShift'])
                ->orderBy('started_at', 'desc');

            $employees = Employee::where('company_id', $companyId)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $totalShifts = ParkingShift::where('company_id', $companyId)->count();
            $totalIncome = ParkingShift::where('company_id', $companyId)
                ->where('status', 'closed')
                ->sum('incomes_total');
            $totalMovements = ParkingShift::where('company_id', $companyId)
                ->where('status', 'closed')
                ->sum('total_movements');
        }

        // Filtros
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        // Paginación
        $shifts = $query->paginate(20);

        $stats = [
            'total_shifts' => $totalShifts,
            'total_income' => $totalIncome,
            'total_movements' => $totalMovements,
        ];

        return view('parking.shifts.audit', compact('shifts', 'employees', 'stats', 'isMaster'));
    }

    /**
     * Muestra el detalle de un turno específico
     */
    public function show(ParkingShift $shift)
    {
        $user = Auth::user();
        $isMaster = $user->isMaster();

        // Si no es master, verificar que el turno pertenece a su empresa
        if (!$isMaster) {
            $companyId = $user->isCompany() ? $user->id : $user->parent_id;

            if ($shift->company_id !== $companyId) {
                abort(403, 'No tienes permiso para ver este turno.');
            }
        }

        // Cargar relaciones
        $shift->load(['employee', 'previousShift', 'stays.parkingSpace', 'stays.discount', 'stays.paymentMethods', 'cashMovements']);

        // Obtener estadísticas
        $shiftService = new ShiftManagementService();
        $stats = $shiftService->getShiftStatistics($shift);

        return view('parking.shifts.show', compact('shift', 'stats', 'isMaster'));
    }
}

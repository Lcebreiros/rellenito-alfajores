<?php

namespace App\Http\Controllers;

use App\Models\ParkingStay;
use App\Models\ParkingSpace;
use App\Models\ParkingSpaceCategory;
use App\Models\Employee;
use App\Models\ParkingShift;
use App\Models\Service;
use App\Models\Discount;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ParkingPricingService;
use App\Services\ThermalPrinterService;
use Illuminate\Support\Str;

class ParkingStayController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()?->hasModule('parking')) {
                abort(404);
            }
            return $next($request);
        });
    }

    /**
     * Listado de estadías de estacionamiento (básico)
     * Solo accesible para empresa y master
     */
    public function index()
    {
        $user = Auth::user();

        // Solo permitir acceso a empresa y master
        if (!$user->isCompany() && !$user->isMaster()) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $isMaster = $user->isMaster();
        $companyId = $this->currentCompanyId();

        // Si es master, obtener todos los datos
        if ($isMaster) {
            $openStays = ParkingStay::with(['parkingSpace', 'paymentMethods', 'discount'])
                ->where('status', 'open')
                ->orderByDesc('entry_at')
                ->get();

            $stays = ParkingStay::with(['parkingSpace', 'paymentMethods', 'discount'])
                ->orderByDesc('created_at')
                ->paginate(20);

            $spaces = ParkingSpace::orderBy('name')->get();
            $discounts = Discount::where('is_active', true)->orderBy('name')->get();
            $openShift = ParkingShift::whereNull('ended_at')->latest('started_at')->first();
            $recentShifts = ParkingShift::where('status', 'closed')->orderByDesc('started_at')->paginate(20);
        } else {
            $openStays = ParkingStay::with(['parkingSpace', 'paymentMethods', 'discount'])
                ->where('company_id', $companyId)
                ->where('status', 'open')
                ->orderByDesc('entry_at')
                ->get();

            $stays = ParkingStay::with(['parkingSpace', 'paymentMethods', 'discount'])
                ->where('company_id', $companyId)
                ->orderByDesc('created_at')
                ->paginate(20);

            $spaces = ParkingSpace::where('company_id', $companyId)->orderBy('name')->get();
            $discounts = Discount::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
            $openShift = ParkingShift::where('company_id', $companyId)->whereNull('ended_at')->latest('started_at')->first();
            $recentShifts = ParkingShift::where('company_id', $companyId)->where('status', 'closed')->orderByDesc('started_at')->paginate(20);
        }

        return view('parking.stays.index', compact('stays', 'openStays', 'spaces', 'discounts', 'openShift', 'recentShifts', 'isMaster'));
    }

    /**
     * Tablero visual de cocheras (cards por categoría)
     */
    public function board()
    {
        $user = Auth::user();
        $isMaster = $user && $user->isMaster();
        $companyId = $this->currentCompanyId();

        // Si es master, obtener todos los datos sin filtrar por company
        if ($isMaster) {
            $categories = ParkingSpaceCategory::orderBy('name')->get();
            $spaces = ParkingSpace::with(['category', 'rate', 'service'])->orderBy('name')->get();
            $openStays = ParkingStay::where('status', 'open')->get()->keyBy('parking_space_id');
            $discounts = Discount::where('is_active', true)->orderBy('name')->get();
            $openShift = ParkingShift::whereNull('ended_at')->latest('started_at')->first();
            $recentShifts = ParkingShift::orderByDesc('started_at')->limit(5)->get();
            $employees = Employee::orderBy('first_name')->select('id', 'first_name', 'last_name', 'company_id')->get();
        } else {
            $categories = ParkingSpaceCategory::where('company_id', $companyId)->orderBy('name')->get();
            $spaces = ParkingSpace::with(['category', 'rate', 'service'])->where('company_id', $companyId)->orderBy('name')->get();
            $openStays = ParkingStay::where('company_id', $companyId)->where('status', 'open')->get()->keyBy('parking_space_id');
            $discounts = Discount::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
            $openShift = ParkingShift::where('company_id', $companyId)->whereNull('ended_at')->latest('started_at')->first();
            $recentShifts = ParkingShift::where('company_id', $companyId)->orderByDesc('started_at')->limit(5)->get();
            $employees = Employee::where('company_id', $companyId)->orderBy('first_name')->select('id', 'first_name', 'last_name')->get();
        }

        // Solo los medios activados para el usuario (globales activados + propios activos)
        $paymentMethods = PaymentMethod::availableForUser($user)->ordered()->get();

        return view('parking.board', compact('categories', 'spaces', 'openStays', 'discounts', 'openShift', 'recentShifts', 'employees', 'paymentMethods', 'isMaster'));
    }

    /**
     * Ingreso/Egreso rápido por patente
     */
    public function check(Request $request, ParkingPricingService $pricingService, ThermalPrinterService $printerService)
    {
        $companyId = $this->currentCompanyId();
        $data = $request->validate([
            'license_plate' => 'required|string|max:15',
            'vehicle_type' => 'nullable|string|max:50',
            // Sin selector manual de tarifa en el check-in rápido
            'parking_space_id' => 'nullable|exists:parking_spaces,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'payment_method_ids' => 'nullable|array',
            'payment_method_ids.*' => 'integer',
        ]);

        $plate = Str::upper(trim($data['license_plate']));
        $rateId = null;
        $vehicleType = $data['vehicle_type'] ?? null;
        $spaceId = $data['parking_space_id'] ?? null;
        $selectedPaymentMethods = $data['payment_method_ids'] ?? [];
        $space = null;
        $discount = null;
        if (!empty($data['discount_id'])) {
            $discount = Discount::where('company_id', $companyId)->findOrFail($data['discount_id']);
            if (!$discount->isActiveNow()) {
                return back()->withErrors('El bono no está activo.');
            }
        }

        if ($spaceId) {
            $space = ParkingSpace::where('company_id', $companyId)->findOrFail($spaceId);
            if ($space->usage === ParkingSpace::USAGE_MONTHLY) {
                return back()->withErrors('Esta cochera es solo para abonos mensuales.');
            }
            if ($space->status === ParkingSpace::STATUS_BUSY || $space->status === ParkingSpace::STATUS_MAINTENANCE) {
                return back()->withErrors('La cochera seleccionada no está disponible.');
            }
            $rateId = $this->findRateForVehicle($companyId, $vehicleType, $space->rate_id);
        }

        $openStay = ParkingStay::where('company_id', $companyId)
            ->where('license_plate', $plate)
            ->where('status', 'open')
            ->latest('entry_at')
            ->first();

        if ($openStay) {
            $openStay->exit_at = now();
            if (!$openStay->rate_id && $rateId) {
                $openStay->rate_id = $rateId;
            }
            if (!$openStay->parking_space_id && $space) {
                $openStay->parking_space_id = $space->id;
            }
            if (!$openStay->vehicle_type && $vehicleType) {
                $openStay->vehicle_type = $vehicleType;
            }
            if ($discount && !$openStay->discount_id) {
                $openStay->discount_id = $discount->id;
            }
            $openStay->loadMissing('parkingSpace.service');
            // Si no hay tarifa, cobramos el precio del servicio asociado (igual que closeSpace)
            if (!$openStay->rate_id && $openStay->parkingSpace && $openStay->parkingSpace->service) {
                $servicePrice = (float) ($openStay->parkingSpace->service->price ?? 0);
                $openStay->total_amount = $servicePrice;
                $openStay->pricing_breakdown = [
                    ['label' => 'servicio', 'price' => $servicePrice],
                ];
                $openStay->status = 'closed';
                $openStay->save();
            } else {
                $pricingService->closeStay($openStay, $discount);
            }
            if ($openStay->parkingSpace && $openStay->parkingSpace->status === ParkingSpace::STATUS_BUSY) {
                $openStay->parkingSpace->update(['status' => ParkingSpace::STATUS_AVAILABLE]);
            }

            $this->syncPaymentMethods($openStay, $selectedPaymentMethods);

            // Al egreso NO se imprime ticket, se escanea el ticket de ingreso

            return back()->with('ok', 'Salida registrada. Total: $ ' . number_format((float) $openStay->total_amount, 2, ',', '.'));
        }

        $stay = ParkingStay::create([
            'company_id' => $companyId,
            'rate_id' => $rateId,
            'parking_space_id' => $space?->id,
            'discount_id' => $discount?->id,
            'license_plate' => $plate,
            'vehicle_type' => $vehicleType,
            'entry_at' => now(),
            'status' => 'open',
        ]);

        if ($space) {
            $space->update(['status' => ParkingSpace::STATUS_BUSY]);
        }

        // Imprimir ticket de ingreso automáticamente
        if (config('thermal_printer.auto_print.parking_entry', true)) {
            $printerService->printParkingTicket($stay);
        }

        return back()->with('ok', 'Ingreso registrado para ' . $plate);
    }

    /**
     * Apertura rápida de estadía desde card de cochera
     */
    public function openSpace(Request $request, ParkingSpace $parkingSpace, ThermalPrinterService $printerService)
    {
        $this->authorizeSpace($parkingSpace);

        $data = $request->validate([
            'license_plate' => 'required|string|max:15',
            'vehicle_type' => 'nullable|string|max:50',
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        if ($parkingSpace->status === ParkingSpace::STATUS_BUSY || $parkingSpace->status === ParkingSpace::STATUS_MAINTENANCE) {
            return back()->withErrors('La cochera no está disponible.');
        }
        if ($parkingSpace->usage === ParkingSpace::USAGE_MONTHLY) {
            return back()->withErrors('Esta cochera es solo para abonos mensuales.');
        }

        $companyId = $this->currentCompanyId();
        $plate = Str::upper(trim($data['license_plate']));
        $rateId = $parkingSpace->rate_id;
        $vehicleType = $data['vehicle_type'] ?? null;
        $rateId = $this->findRateForVehicle($companyId, $vehicleType, $rateId);
        $discount = null;

        $existingPlate = ParkingStay::where('company_id', $companyId)
            ->where('license_plate', $plate)
            ->where('status', 'open')
            ->first();
        if ($existingPlate) {
            return back()->withErrors('Ya hay una estadía abierta para esta patente.');
        }

        if (!empty($data['discount_id'])) {
            $discount = Discount::where('company_id', $companyId)->findOrFail($data['discount_id']);
            if (!$discount->isActiveNow()) {
                return back()->withErrors('El bono no está activo.');
            }
        }

        $stay = ParkingStay::create([
            'company_id' => $companyId,
            'parking_space_id' => $parkingSpace->id,
            'rate_id' => $rateId,
            'discount_id' => $discount?->id,
            'license_plate' => $plate,
            'vehicle_type' => $vehicleType,
            'entry_at' => now(),
            'status' => 'open',
        ]);

        $parkingSpace->update(['status' => ParkingSpace::STATUS_BUSY]);

        // Imprimir ticket de ingreso automáticamente
        if (config('thermal_printer.auto_print.parking_entry', true)) {
            $printerService->printParkingTicket($stay);
        }

        return back()->with('ok', 'Ingreso registrado en ' . $parkingSpace->name);
    }

    /**
     * Cierre rápido de estadía desde card de cochera
     */
    public function closeSpace(Request $request, ParkingSpace $parkingSpace, ParkingPricingService $pricingService, ThermalPrinterService $printerService)
    {
        $this->authorizeSpace($parkingSpace);

        $request->validate([
            'payment_method_ids' => 'nullable|array',
            'payment_method_ids.*' => 'integer',
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        $companyId = $this->currentCompanyId();
        $stay = ParkingStay::where('company_id', $companyId)
            ->where('parking_space_id', $parkingSpace->id)
            ->where('status', 'open')
            ->latest('entry_at')
            ->first();

        if (!$stay) {
            return back()->withErrors('No hay una estadía abierta en esta cochera.');
        }

        $discount = $stay->discount;
        if ($request->filled('discount_id')) {
            $discount = Discount::where('company_id', $companyId)->findOrFail($request->input('discount_id'));
            if (!$discount->isActiveNow()) {
                return back()->withErrors('El bono no está activo.');
            }
            $stay->discount_id = $discount->id;
        }

        $stay->exit_at = now();
        if (!$stay->rate_id && $parkingSpace->rate_id) {
            $stay->rate_id = $parkingSpace->rate_id;
        }

        // Si tiene servicio asociado y no hay tarifa, cobrar precio del servicio
        if (!$stay->rate_id && $parkingSpace->service) {
            $stay->total_amount = (float) ($parkingSpace->service->price ?? 0);
            $stay->pricing_breakdown = [
                ['label' => 'servicio', 'price' => (float) ($parkingSpace->service->price ?? 0)],
            ];
            $stay->status = 'closed';
            $stay->save();
        } else {
            $pricingService->closeStay($stay, $discount);
        }

        if ($parkingSpace->status === ParkingSpace::STATUS_BUSY) {
            $parkingSpace->update(['status' => ParkingSpace::STATUS_AVAILABLE]);
        }

        $this->syncPaymentMethods($stay, $request->input('payment_method_ids', []));

        // Al egreso NO se imprime ticket, se escanea el ticket de ingreso

        return back()->with('ok', 'Salida registrada en ' . $parkingSpace->name);
    }

    /**
     * Iniciar un turno de caja de estacionamiento.
     */
    public function startShift(Request $request)
    {
        $companyId = $this->currentCompanyId();

        $exists = ParkingShift::where('company_id', $companyId)
            ->whereNull('ended_at')
            ->exists();
        if ($exists) {
            return back()->withErrors('Ya hay un turno abierto. Primero cerralo.');
        }

        $data = $request->validate([
            'operator_name' => 'nullable|string|max:100',
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $employeeId = $data['employee_id'] ?? null;
        if ($employeeId) {
            $employee = Employee::where('company_id', $companyId)->findOrFail($employeeId);
            $operatorName = trim($employee->first_name . ' ' . $employee->last_name) ?: $employee->first_name ?: $employee->last_name;
        } else {
            $operatorName = $data['operator_name'] ?? null;
        }

        if (!$operatorName) {
            return back()->withErrors('Ingresá un nombre de operador o seleccioná un empleado.');
        }

        // Tomar efectivo restante del turno anterior (si existe)
        $previousShift = ParkingShift::where('company_id', $companyId)
            ->whereNotNull('ended_at')
            ->where('status', 'closed')
            ->latest('ended_at')
            ->first();

        $initialCash = 0;
        if ($previousShift) {
            // Priorizar remaining_cash; si no existe, usar cash_counted - envelope_amount
            $initialCash = (float) ($previousShift->remaining_cash ?? 0);
            if ($initialCash === 0.0) {
                $initialCash = (float) ($previousShift->cash_counted - ($previousShift->envelope_amount ?? 0));
            }
        }

        ParkingShift::create([
            'company_id' => $companyId,
            'operator_name' => $operatorName,
            'employee_id' => $employeeId,
            'previous_shift_id' => $previousShift?->id,
            'started_at' => now(),
            'status' => 'open',
            'initial_cash' => $initialCash,
            'expected_cash' => $initialCash,
        ]);

        return back()->with('ok', 'Turno iniciado.');
    }

    /**
     * Cerrar turno y generar reporte.
     */
    public function closeShift(Request $request)
    {
        $companyId = $this->currentCompanyId();

        $shift = ParkingShift::where('company_id', $companyId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (!$shift) {
            return back()->withErrors('No hay un turno abierto.');
        }

        $data = $request->validate([
            'cash_counted' => 'required|numeric|min:0',
            'envelope_amount' => 'nullable|numeric|min:0',
            'mp_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $endedAt = now();

        $stays = ParkingStay::with(['parkingSpace', 'discount'])
            ->where('company_id', $companyId)
            ->where('status', 'closed')
            ->whereBetween('exit_at', [$shift->started_at, $endedAt])
            ->orderBy('exit_at')
            ->get();

        $incomesTotal = $stays->sum('total_amount');

        $shift->ended_at = $endedAt;
        $shift->incomes_total = $incomesTotal;
        $shift->cash_counted = $data['cash_counted'];
        $shift->envelope_amount = $data['envelope_amount'] ?? 0;
        $shift->mp_amount = $data['mp_amount'] ?? 0;
        $shift->notes = $data['notes'] ?? null;
        $shift->status = 'closed';

        // Calcular efectivo esperado y remanente
        $cashPayments = $shift->incomes_total - $shift->mp_amount;

        // IMPORTANTE: Sumar ingresos de caja y restar egresos de caja
        $cashIngresos = $shift->cashMovements()->where('type', 'ingreso')->sum('amount');
        $cashEgresos = $shift->cashMovements()->where('type', 'egreso')->sum('amount');

        $shift->expected_cash = ($shift->initial_cash ?? 0) + $cashPayments + $cashIngresos - $cashEgresos;
        $shift->remaining_cash = max(0, $shift->cash_counted - $shift->envelope_amount);
        $shift->cash_difference = $shift->cash_counted - $shift->expected_cash;

        $shift->file_path = $this->exportShiftReport($shift, $stays, $data);
        $shift->save();

        $reportMeta = [
            'operator' => $shift->operator_name,
            'started_at' => optional($shift->started_at)->format('d/m/Y H:i'),
            'ended_at' => $endedAt->format('d/m/Y H:i'),
            'incomes_total' => (float) $incomesTotal,
            'cash_counted' => (float) $shift->cash_counted,
            'envelope_amount' => (float) $shift->envelope_amount,
            'mp_amount' => (float) $shift->mp_amount,
        ];

        return back()
            ->with('ok', 'Turno cerrado y reporte guardado.')
            ->with('shiftReportUrl', route('parking.shifts.download', $shift))
            ->with('shiftReportMeta', $reportMeta);
    }

    /**
     * Descargar reporte generado.
     */
    public function downloadShift(ParkingShift $shift)
    {
        if ((int) $shift->company_id !== (int) $this->currentCompanyId()) {
            abort(404);
        }

        if (!$shift->file_path || !Storage::disk('local')->exists($shift->file_path)) {
            return back()->withErrors('Archivo no disponible.');
        }

        return response()->download(Storage::disk('local')->path($shift->file_path));
    }

    /**
     * Generar CSV (Excel-friendly) del turno.
     */
    private function exportShiftReport(ParkingShift $shift, $stays, array $data): string
    {
        $rows = [];
        $totalMovements = 0;
        $reportDate = $shift->started_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        foreach ($stays as $stay) {
            $detail = '';
            if ($stay->discount) {
                $detail = 'Bono: ' . $stay->discount->name;
            }
            $rows[] = [
                $reportDate,
                trim(strtoupper($stay->license_plate) . ' ' . ($stay->vehicle_type ?? '')),
                $detail,
                (float) $stay->total_amount,
                '',
                '',
            ];
            $totalMovements += (float) $stay->total_amount;
        }

        // Total en la última fila de movimientos
        if (!empty($rows)) {
            $rows[count($rows) - 1][5] = $totalMovements;
        }

        // Caja contada
        $rows[] = [
            $reportDate,
            'Caja',
            'Conteo manual',
            '',
            '',
            (float) ($data['cash_counted'] ?? 0),
        ];

        // Egreso sobre jefes
        if (!empty($data['envelope_amount'])) {
            $rows[] = [
                $reportDate,
                'Sobre',
                'Puerta (jefes)',
                '',
                (float) $data['envelope_amount'],
                '',
            ];
        }

        // MP informativo
        if (!empty($data['mp_amount'])) {
            $rows[] = [
                $reportDate,
                'MP',
                'Cobrado por Mercado Pago (no efectivo)',
                '',
                (float) $data['mp_amount'],
                '',
            ];
        }

        $filename = 'parking/shifts/' . $shift->id . '-' . ($shift->started_at?->format('Ymd_His') ?? now()->format('Ymd_His')) . '.csv';

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Fecha', 'Patente', 'Detalle', 'Ingreso', 'Egreso', 'Total']);
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($filename, $csv);

        return $filename;
    }

    private function syncPaymentMethods(ParkingStay $stay, array $methodIds): void
    {
        $ids = collect($methodIds)->filter()->unique()->map(fn($id) => (int) $id);
        if ($ids->isEmpty()) {
            $stay->paymentMethods()->detach();
            return;
        }

        $allowedIds = PaymentMethod::availableForUser(Auth::user())
            ->whereIn('id', $ids)
            ->pluck('id');

        $finalIds = $ids->intersect($allowedIds);
        if ($finalIds->isEmpty()) {
            $stay->paymentMethods()->detach();
            return;
        }

        $amountPerMethod = $stay->total_amount > 0 && $finalIds->count() > 0
            ? ((float) $stay->total_amount) / $finalIds->count()
            : 0;

        $pivotData = [];
        foreach ($finalIds as $pmId) {
            $pivotData[$pmId] = [
                'amount' => $amountPerMethod,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $stay->paymentMethods()->sync($pivotData);
    }

    private function findRateForVehicle(int $companyId, ?string $vehicleType, ?int $fallbackRateId): ?int
    {
        $type = trim(mb_strtolower((string) $vehicleType));
        if ($type !== '') {
            $match = Rate::where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNotNull('vehicle_type')
                ->whereRaw('LOWER(vehicle_type) = ?', [$type])
                ->value('id');
            if ($match) {
                return $match;
            }
        }

        if ($fallbackRateId) {
            return $fallbackRateId;
        }

        // Fallback: tarifa "Hora" por defecto para movimientos diarios
        return $this->defaultHourlyRateId($companyId);
    }

    /**
     * Tarifa por defecto "Hora" (nombre que contenga "hora"), activa y de la empresa.
     */
    private function defaultHourlyRateId(int $companyId): ?int
    {
        return Rate::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereRaw('LOWER(name) LIKE ?', ['hora%'])
            ->value('id');
    }

    private function authorizeSpace(ParkingSpace $space): void
    {
        if ((int) $space->company_id !== (int) $this->currentCompanyId()) {
            abort(404);
        }
    }

    private function currentCompanyId(): int
    {
        $user = Auth::user();

        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) Auth::id();
    }
}

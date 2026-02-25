<?php

namespace App\Livewire\Parking;

use Livewire\Component;
use App\Models\ParkingStay;
use App\Models\ParkingSpace;
use App\Models\ParkingShift;
use App\Models\Discount;
use App\Models\PaymentMethod;
use App\Models\Employee;
use App\Models\CashMovement;
use App\Services\ParkingPricingService;
use App\Services\ThermalPrinterService;
use App\Services\ShiftManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OperatorPanel extends Component
{
    // Gestión de turnos
    public $currentShift = null;
    public $showOpenShiftModal = false;
    public $showCloseShiftModal = false;
    public $operatorName = '';
    public $employeeId = null;
    public $initialCash = 0;
    public $actualCash = 0;
    public $envelopeAmount = 0;
    public $closeNotes = '';

    // Formulario de ingreso
    public $licensePlate = '';
    public $vehicleType = 'Auto';
    public $selectedRateId = null;

    // Campo scanner
    public $scannerInput = '';

    // Modal de egreso
    public $showExitModal = false;
    public $exitStay = null;
    public $exitData = [];
    public $useMercadoPago = false;
    public $selectedDiscountId = null;
    public $selectedPaymentMethodIds = [];

    // Sistema de validación inteligente
    public $showDuplicateWarning = false;
    public $duplicateStay = null;
    public $showMultipleMovementsModal = false;
    public $multipleMovements = [];

    // Movimientos de caja
    public $showCashMovementModal = false;
    public $cashMovementType = 'ingreso'; // ingreso o egreso
    public $cashMovementAmount = '';
    public $cashMovementDescription = '';
    public $cashMovementNotes = '';

    // Listeners
    protected $listeners = ['refreshMovements' => '$refresh', 'shiftUpdated' => 'loadCurrentShift'];

    public function mount()
    {
        // Cargar turno actual
        $this->loadCurrentShift();

        // Seleccionar tarifa por defecto automáticamente
        $this->loadDefaultRate();
    }

    /**
     * Cargar tarifa por defecto (la primera activa de parking)
     */
    public function loadDefaultRate()
    {
        $companyId = $this->currentCompanyId();
        $defaultRate = \App\Models\Rate::where('company_id', $companyId)
            ->where('rental_type', 'parking')
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();

        if ($defaultRate) {
            $this->selectedRateId = $defaultRate->id;
        }
    }

    /**
     * Cargar el turno abierto actual si existe
     */
    public function loadCurrentShift()
    {
        $shiftService = new ShiftManagementService();
        $this->currentShift = $shiftService->getCurrentOpenShift($this->currentCompanyId());
    }

    /**
     * Mostrar modal para abrir turno
     */
    public function showOpenShiftForm()
    {
        $shiftService = new ShiftManagementService();
        $previousShift = $shiftService->findPreviousShift($this->currentCompanyId());

        // Sugerir efectivo inicial basado en turno anterior
        $this->initialCash = $previousShift?->remaining_cash ?? 0;

        $this->showOpenShiftModal = true;
    }

    /**
     * Abrir nuevo turno
     */
    public function openShift()
    {
        $this->validate([
            'employeeId' => 'required|exists:employees,id',
            'initialCash' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $companyId = $this->currentCompanyId();

        // Obtener el empleado seleccionado
        $employee = Employee::where('id', $this->employeeId)
            ->where('company_id', $companyId)
            ->first();

        if (!$employee) {
            session()->flash('error', 'Empleado no encontrado o no pertenece a esta empresa.');
            return;
        }

        try {
            $shiftService = new ShiftManagementService();

            // Obtener la empresa correctamente
            if ($user->isMaster()) {
                // Para master, buscar la empresa por ID
                $company = \App\Models\User::find($companyId);
            } elseif ($user->isCompany()) {
                $company = $user;
            } else {
                $company = $user->parent;
            }

            // Usar el nombre completo del empleado
            $operatorName = trim($employee->first_name . ' ' . $employee->last_name) ?: $employee->first_name;

            $this->currentShift = $shiftService->openShift(
                $employee,
                $company,
                $this->initialCash,
                $operatorName
            );

            $this->showOpenShiftModal = false;
            $this->reset(['initialCash', 'employeeId']);

            session()->flash('success', 'Turno iniciado correctamente.');
            $this->dispatch('shiftUpdated');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Mostrar modal para cerrar turno
     */
    public function showCloseShiftForm()
    {
        if (!$this->currentShift) {
            session()->flash('error', 'No hay un turno abierto.');
            return;
        }

        // Recalcular totales antes de mostrar el formulario de cierre
        $this->currentShift->recalculateTotals();

        $this->actualCash = 0;
        $this->envelopeAmount = 0;
        $this->closeNotes = '';
        $this->showCloseShiftModal = true;
    }

    /**
     * Cerrar turno actual
     */
    public function closeShift()
    {
        $this->validate([
            'actualCash' => 'required|numeric|min:0',
            'envelopeAmount' => 'required|numeric|min:0',
        ]);

        if (!$this->currentShift) {
            session()->flash('error', 'No hay un turno abierto.');
            return;
        }

        try {
            $shiftService = new ShiftManagementService();

            $shiftService->closeShift(
                $this->currentShift,
                $this->actualCash,
                $this->envelopeAmount,
                $this->closeNotes
            );

            $this->showCloseShiftModal = false;
            $this->currentShift = null;
            $this->reset(['actualCash', 'envelopeAmount', 'closeNotes']);

            session()->flash('success', 'Turno cerrado correctamente.');
            $this->dispatch('shiftUpdated');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Cancelar apertura de turno
     */
    public function cancelOpenShift()
    {
        $this->showOpenShiftModal = false;
        $this->reset(['initialCash', 'operatorName', 'employeeId']);
    }

    /**
     * Cancelar cierre de turno
     */
    public function cancelCloseShift()
    {
        $this->showCloseShiftModal = false;
        $this->reset(['actualCash', 'envelopeAmount', 'closeNotes']);
    }

    public function render()
    {
        $companyId = $this->currentCompanyId();

        // Descuentos activos (bonificaciones de restaurantes)
        $discounts = Discount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Empleados de la empresa para selección en apertura de turno
        $employees = Employee::where('company_id', $companyId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Movimientos para mostrar en la vista
        if ($this->currentShift) {
            // Si hay turno abierto, mostrar:
            // 1. TODOS los movimientos PENDIENTES (pueden ser del turno anterior - estadías largas 12h/24h)
            // 2. Solo los COMPLETADOS del turno ACTUAL
            $pendingMovements = ParkingStay::with(['parkingSpace'])
                ->where('company_id', $companyId)
                ->where('status', 'open')
                ->orderBy('entry_at', 'asc') // Los más antiguos primero (los que llevan más tiempo)
                ->get();

            $completedMovements = ParkingStay::with(['parkingSpace'])
                ->where('parking_shift_id', $this->currentShift->id)
                ->where('status', 'closed')
                ->orderByDesc('exit_at')
                ->get();

            // Combinar: primero pendientes, luego completados
            $recentMovements = $pendingMovements->concat($completedMovements);
        } else {
            // Si NO hay turno abierto, no mostrar movimientos
            // El operario debe abrir turno primero
            $recentMovements = collect();
        }

        // Medios de pago disponibles
        $paymentMethods = PaymentMethod::availableForUser(Auth::user())
            ->ordered()
            ->get();

        // Estadísticas del turno actual
        $shiftStats = null;
        $previousShift = null;

        if ($this->currentShift) {
            $shiftService = new ShiftManagementService();
            $shiftStats = $shiftService->getShiftStatistics($this->currentShift);
            $previousShift = $this->currentShift->previousShift;
        }

        return view('livewire.parking.operator-panel', [
            'discounts' => $discounts,
            'employees' => $employees,
            'recentMovements' => $recentMovements,
            'paymentMethods' => $paymentMethods,
            'shiftStats' => $shiftStats,
            'previousShift' => $previousShift,
        ]);
    }

    /**
     * Procesar input del scanner
     * Detecta si es un código de barras (números de 10 dígitos)
     */
    public function processScannerInput()
    {
        $input = trim($this->scannerInput);

        // Si está vacío, ignorar
        if (empty($input)) {
            return;
        }

        // Detectar si es un código de barras (10 dígitos numéricos)
        if (preg_match('/^\d{10}$/', $input)) {
            // Es un código de barras - buscar estadía por ID
            $stayId = (int) ltrim($input, '0');
            $this->processExit($stayId);
        } else {
            // Es entrada manual de patente
            $this->licensePlate = strtoupper($input);
        }

        // Limpiar campo scanner
        $this->scannerInput = '';
    }

    /**
     * Verificar e intentar crear ingreso (con validación inteligente)
     */
    public function checkForEntry()
    {
        // Verificar que haya un turno abierto
        if (!$this->currentShift) {
            session()->flash('error', 'Debe abrir un turno antes de registrar ingresos.');
            return;
        }

        $this->validate([
            'licensePlate' => 'required|string|max:15',
            'vehicleType' => 'required|string',
        ]);

        $companyId = $this->currentCompanyId();
        $plate = Str::upper(trim($this->licensePlate));

        // Verificar si ya existe una estadía abierta
        $existing = ParkingStay::where('company_id', $companyId)
            ->where('license_plate', $plate)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            // Mostrar modal de warning con opciones
            $this->duplicateStay = $existing;
            $this->showDuplicateWarning = true;
            return;
        }

        // No hay duplicado, crear directamente
        $this->createEntry();
    }

    /**
     * Crear nuevo ingreso (sin validación, llamado internamente)
     */
    public function createEntry($forceDuplicate = false)
    {
        $companyId = $this->currentCompanyId();
        $plate = Str::upper(trim($this->licensePlate));

        // Si no es forzado, validar nuevamente
        if (!$forceDuplicate) {
            $existing = ParkingStay::where('company_id', $companyId)
                ->where('license_plate', $plate)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                session()->flash('error', 'Ya existe una estadía abierta para esta patente.');
                return;
            }
        }

        // Buscar la tarifa apropiada según el tipo de vehículo
        $rateId = $this->selectedRateId;
        if (!$rateId) {
            $this->loadDefaultRate();
            $rateId = $this->selectedRateId;
        }

        // Intentar encontrar tarifa específica para el tipo de vehículo
        $rateFinderService = new \App\Services\RateFinderService();
        $rateId = $rateFinderService->findRateForVehicle(
            $companyId,
            $this->vehicleType,
            $rateId // fallback a la tarifa por defecto si no encuentra
        );

        // Crear estadía vinculada al turno actual con la tarifa seleccionada
        $stay = ParkingStay::create([
            'company_id' => $companyId,
            'parking_shift_id' => $this->currentShift->id,
            'parking_space_id' => null, // Sin cocheras por ahora
            'rate_id' => $rateId,
            'license_plate' => $plate,
            'vehicle_type' => $this->vehicleType,
            'entry_at' => now(),
            'status' => 'open',
        ]);

        // Imprimir ticket automáticamente (si está configurado)
        if (config('thermal_printer.auto_print.parking_entry', false)) {
            $printerService = new ThermalPrinterService();
            $printerService->printParkingTicket($stay);
        }

        // Cerrar modal de warning si estaba abierto
        $this->showDuplicateWarning = false;
        $this->duplicateStay = null;

        // Limpiar formulario
        $this->reset(['licensePlate', 'vehicleType']);

        session()->flash('success', "Ingreso registrado: {$plate}");

        // Refrescar movimientos y turno
        $this->dispatch('refreshMovements');
        $this->loadCurrentShift();
    }

    /**
     * Verificar e intentar procesar egreso (con validación inteligente)
     */
    public function checkForExit()
    {
        // Verificar que haya un turno abierto
        if (!$this->currentShift) {
            session()->flash('error', 'Debe abrir un turno antes de registrar egresos.');
            return;
        }

        $this->validate([
            'licensePlate' => 'required|string|max:15',
        ]);

        $companyId = $this->currentCompanyId();
        $plate = Str::upper(trim($this->licensePlate));

        // Buscar todos los movimientos abiertos con esa patente
        $openMovements = ParkingStay::with(['parkingSpace'])
            ->where('company_id', $companyId)
            ->where('license_plate', $plate)
            ->where('status', 'open')
            ->orderBy('entry_at', 'asc')
            ->get();

        if ($openMovements->isEmpty()) {
            session()->flash('error', "No hay movimientos abiertos con la patente {$plate}");
            return;
        }

        if ($openMovements->count() === 1) {
            // Solo hay uno, procesarlo directamente
            $this->processExit($openMovements->first()->id);
        } else {
            // Hay múltiples, mostrar modal de selección
            $this->multipleMovements = $openMovements;
            $this->showMultipleMovementsModal = true;
        }
    }

    /**
     * Seleccionar un movimiento específico para egreso (desde modal de múltiples)
     */
    public function selectMovementForExit($stayId)
    {
        $this->showMultipleMovementsModal = false;
        $this->multipleMovements = [];
        $this->processExit($stayId);
    }

    /**
     * Cancelar modal de múltiples movimientos
     */
    public function cancelMultipleMovements()
    {
        $this->showMultipleMovementsModal = false;
        $this->multipleMovements = [];
    }

    /**
     * Cancelar modal de warning de duplicado
     */
    public function cancelDuplicateWarning()
    {
        $this->showDuplicateWarning = false;
        $this->duplicateStay = null;
    }

    /**
     * Crear ingreso duplicado (desde modal de warning)
     */
    public function createDuplicateEntry()
    {
        $this->createEntry(true);
    }

    /**
     * Marcar egreso del movimiento duplicado existente (desde modal de warning)
     */
    public function exitDuplicateStay()
    {
        if ($this->duplicateStay) {
            $this->showDuplicateWarning = false;
            $this->processExit($this->duplicateStay->id);
            $this->duplicateStay = null;
        }
    }

    /**
     * Procesar egreso al escanear código de barras
     */
    public function processExit($stayId)
    {
        $companyId = $this->currentCompanyId();

        $stay = ParkingStay::with(['parkingSpace', 'rate', 'discount'])
            ->where('company_id', $companyId)
            ->where('id', $stayId)
            ->where('status', 'open')
            ->first();

        if (!$stay) {
            session()->flash('error', 'No se encontró una estadía abierta con ese código.');
            return;
        }

        // Preparar datos para el modal
        $stay->exit_at = now();

        // Calcular precio
        $result = $stay->calculateTotal($stay->discount);

        $this->exitStay = $stay;
        $this->exitData = [
            'stay_id' => $stay->id,
            'license_plate' => $stay->license_plate,
            'vehicle_type' => $stay->vehicle_type ?? 'Auto',
            'space_name' => $stay->parkingSpace?->name ?? '-',
            'entry_at' => $stay->entry_at->format('d/m/Y H:i'),
            'exit_at' => $stay->exit_at->format('d/m/Y H:i'),
            'duration_minutes' => $stay->entry_at->diffInMinutes($stay->exit_at),
            'duration_formatted' => $this->formatDuration($stay->entry_at->diffInMinutes($stay->exit_at)),
            'total' => $result['total'],
            'breakdown' => $result['breakdown'],
            'discount_amount' => $result['discount_amount'] ?? 0,
        ];

        // Resetear selecciones
        $this->useMercadoPago = false;
        $this->selectedDiscountId = $stay->discount_id;
        $this->selectedPaymentMethodIds = [];

        // Mostrar modal
        $this->showExitModal = true;
    }

    /**
     * Updated when discount changes in modal
     */
    public function updatedSelectedDiscountId($value)
    {
        if (!$this->exitStay) {
            return;
        }

        $companyId = $this->currentCompanyId();
        $discount = null;

        if ($value) {
            $discount = Discount::where('company_id', $companyId)->find($value);
        }

        // IMPORTANTE: Recargar la relación 'rate' porque Livewire puede haberla perdido al serializar
        if (!$this->exitStay->relationLoaded('rate') || !$this->exitStay->rate) {
            $this->exitStay->load('rate');
        }

        // Asegurarse de que exit_at esté seteado
        if (!$this->exitStay->exit_at) {
            $this->exitStay->exit_at = now();
        }

        // Recalcular total con el nuevo descuento
        $result = $this->exitStay->calculateTotal($discount);

        // Debug: Log para verificar
        \Log::info('Discount changed', [
            'discount_id' => $value,
            'has_rate' => $this->exitStay->rate ? true : false,
            'rate_id' => $this->exitStay->rate_id ?? 'null',
            'exit_at' => $this->exitStay->exit_at,
            'total' => $result['total'],
            'discount_amount' => $result['discount_amount'] ?? 0,
            'breakdown_count' => count($result['breakdown']),
        ]);

        // Forzar reactividad de Livewire reasignando el array completo
        $this->exitData = array_merge($this->exitData, [
            'total' => $result['total'],
            'breakdown' => $result['breakdown'],
            'discount_amount' => $result['discount_amount'] ?? 0,
        ]);
    }

    /**
     * Confirmar y cobrar egreso
     */
    public function confirmExit()
    {
        if (!$this->exitStay) {
            return;
        }

        $companyId = $this->currentCompanyId();

        // Aplicar descuento si se seleccionó uno diferente
        $discount = null;
        if ($this->selectedDiscountId) {
            $discount = Discount::where('company_id', $companyId)
                ->findOrFail($this->selectedDiscountId);

            if (!$discount->isActiveNow()) {
                session()->flash('error', 'El descuento seleccionado no está activo.');
                return;
            }

            $this->exitStay->discount_id = $discount->id;
        }

        // Guardar egreso
        $this->exitStay->exit_at = now();
        $pricingService = new ParkingPricingService();
        $pricingService->closeStay($this->exitStay, $discount);

        // Liberar espacio
        if ($this->exitStay->parkingSpace) {
            $this->exitStay->parkingSpace->update(['status' => 'available']);
        }

        // Sincronizar métodos de pago
        $this->syncPaymentMethods();

        $total = $this->exitStay->total_amount;

        // Cerrar modal
        $this->showExitModal = false;
        $this->exitStay = null;
        $this->exitData = [];

        session()->flash('success', "Egreso registrado. Total: $" . number_format($total, 2, ',', '.'));

        // Refrescar movimientos
        $this->dispatch('refreshMovements');
    }

    /**
     * Cancelar modal de egreso
     */
    public function cancelExit()
    {
        $this->showExitModal = false;
        $this->exitStay = null;
        $this->exitData = [];
        $this->useMercadoPago = false;
        $this->selectedDiscountId = null;
        $this->selectedPaymentMethodIds = [];
    }

    /**
     * Sincronizar métodos de pago
     */
    private function syncPaymentMethods()
    {
        if (!$this->exitStay) {
            return;
        }

        // Si marcó Mercado Pago, buscar ese método
        if ($this->useMercadoPago) {
            $mpMethod = PaymentMethod::availableForUser(Auth::user())
                ->where('name', 'LIKE', '%Mercado Pago%')
                ->orWhere('name', 'LIKE', '%MP%')
                ->first();

            if ($mpMethod) {
                $this->selectedPaymentMethodIds = [$mpMethod->id];
            }
        }

        $ids = collect($this->selectedPaymentMethodIds)->filter()->unique()->map(fn($id) => (int) $id);

        if ($ids->isEmpty()) {
            $this->exitStay->paymentMethods()->detach();
            return;
        }

        $allowedIds = PaymentMethod::availableForUser(Auth::user())
            ->whereIn('id', $ids)
            ->pluck('id');

        $finalIds = $ids->intersect($allowedIds);

        if ($finalIds->isEmpty()) {
            $this->exitStay->paymentMethods()->detach();
            return;
        }

        $amountPerMethod = $this->exitStay->total_amount > 0 && $finalIds->count() > 0
            ? ((float) $this->exitStay->total_amount) / $finalIds->count()
            : 0;

        $pivotData = [];
        foreach ($finalIds as $pmId) {
            $pivotData[$pmId] = [
                'amount' => $amountPerMethod,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->exitStay->paymentMethods()->sync($pivotData);
    }

    /**
     * Formatear duración
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $mins . 'min';
    }

    /**
     * Abrir modal de movimiento de caja
     */
    public function openCashMovementModal($type = 'ingreso')
    {
        if (!$this->currentShift) {
            session()->flash('error', 'Debe abrir un turno antes de registrar movimientos de caja.');
            return;
        }

        $this->cashMovementType = $type;
        $this->cashMovementAmount = '';
        $this->cashMovementDescription = '';
        $this->cashMovementNotes = '';
        $this->showCashMovementModal = true;
    }

    /**
     * Cerrar modal de movimiento de caja
     */
    public function cancelCashMovement()
    {
        $this->showCashMovementModal = false;
        $this->cashMovementAmount = '';
        $this->cashMovementDescription = '';
        $this->cashMovementNotes = '';
    }

    /**
     * Guardar movimiento de caja
     */
    public function saveCashMovement()
    {
        $this->validate([
            'cashMovementAmount' => 'required|numeric|min:0.01',
            'cashMovementDescription' => 'required|string|max:255',
            'cashMovementNotes' => 'nullable|string|max:500',
        ], [
            'cashMovementAmount.required' => 'El monto es obligatorio',
            'cashMovementAmount.numeric' => 'El monto debe ser un número',
            'cashMovementAmount.min' => 'El monto debe ser mayor a 0',
            'cashMovementDescription.required' => 'La descripción es obligatoria',
        ]);

        if (!$this->currentShift) {
            session()->flash('error', 'No hay un turno abierto.');
            return;
        }

        CashMovement::create([
            'company_id' => $this->currentCompanyId(),
            'parking_shift_id' => $this->currentShift->id,
            'created_by' => Auth::id(),
            'type' => $this->cashMovementType,
            'amount' => $this->cashMovementAmount,
            'description' => $this->cashMovementDescription,
            'notes' => $this->cashMovementNotes,
        ]);

        $typeLabel = $this->cashMovementType === 'ingreso' ? 'Ingreso' : 'Egreso';
        session()->flash('success', "{$typeLabel} de caja registrado por $" . number_format($this->cashMovementAmount, 2, ',', '.'));

        $this->cancelCashMovement();
        $this->loadCurrentShift();
    }

    /**
     * Obtener ID de la compañía actual
     */
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

<?php

namespace App\Livewire\Rentals;

use App\Models\Booking;
use App\Models\Client;
use App\Models\RentalDurationOption;
use App\Models\RentalSpace;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingCalendar extends Component
{
    public int $selectedMonth;
    public int $selectedYear;
    public ?int $filterSpaceId = null;

    // Modal crear reserva
    public bool $showCreateModal = false;
    public string $createDate = '';
    public ?int $createSpaceId = null;
    public ?int $createDurationOptionId = null;
    public int $createDurationMinutes = 60;
    public string $createStartsAt = '';
    public ?int $createClientId = null;
    public string $createClientName = '';
    public string $createClientPhone = '';
    public float $createTotalAmount = 0;
    public string $createNotes = '';
    public string $createError = '';

    // Fecha seleccionada en el calendario (para slots del día)
    public string $selectedDate = '';

    // Modal detalle de reserva
    public bool $showDetailModal = false;
    public ?int $detailBookingId = null;

    protected $listeners = [
        'bookingUpdated' => '$refresh',
    ];

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
        $this->selectedDate  = now()->toDateString();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear  = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear  = $date->year;
    }

    public function goToToday(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
        $this->selectedDate  = now()->toDateString();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        // Si el día pertenece a otro mes, navegar a ese mes
        $carbon = Carbon::parse($date);
        if ($carbon->month !== $this->selectedMonth || $carbon->year !== $this->selectedYear) {
            $this->selectedMonth = $carbon->month;
            $this->selectedYear  = $carbon->year;
        }
    }

    public function openCreateModalFromSlot(string $date, string $time, int $spaceId): void
    {
        $this->createError            = '';
        $this->createDate             = $date;
        $this->createStartsAt         = $date . ' ' . $time;
        $this->createSpaceId          = $spaceId;
        $this->createDurationOptionId = null;
        $this->createDurationMinutes  = 60;
        $this->createClientId         = null;
        $this->createClientName       = '';
        $this->createClientPhone      = '';
        $this->createTotalAmount      = 0;
        $this->createNotes            = '';
        $this->showCreateModal        = true;
        $this->updatedCreateSpaceId(); // recarga duration options del espacio
    }

    public function openCreateModal(string $date): void
    {
        $this->createError            = '';
        $this->createDate             = $date;
        $this->createStartsAt         = $date . ' 08:00';
        $this->createSpaceId          = null;
        $this->createDurationOptionId = null;
        $this->createDurationMinutes  = 60;
        $this->createClientId         = null;
        $this->createClientName       = '';
        $this->createClientPhone      = '';
        $this->createTotalAmount      = 0;
        $this->createNotes            = '';
        $this->showCreateModal        = true;
    }

    public function updatedCreateDurationOptionId(): void
    {
        if ($this->createDurationOptionId) {
            $option = RentalDurationOption::find($this->createDurationOptionId);
            if ($option) {
                $this->createDurationMinutes = $option->minutes;
                $this->createTotalAmount     = (float) $option->price;
            }
        }
    }

    public function updatedCreateSpaceId(): void
    {
        // Limpiar opción de duración al cambiar de espacio
        $this->createDurationOptionId = null;
        $this->createDurationMinutes  = 60;
        $this->createTotalAmount      = 0;
    }

    public function saveBooking(BookingService $bookingService): void
    {
        $this->createError = '';

        $this->validate([
            'createStartsAt'        => 'required|date',
            'createSpaceId'         => 'required|exists:rental_spaces,id',
            'createDurationMinutes' => 'required|integer|min:15|max:1440',
        ], [
            'createStartsAt.required'        => 'Ingresá la fecha y hora de inicio.',
            'createSpaceId.required'         => 'Seleccioná un espacio.',
            'createDurationMinutes.required' => 'Seleccioná o ingresá la duración.',
        ]);

        $user      = Auth::user();
        $companyId = (method_exists($user, 'isMaster') && $user->isMaster())
            ? null
            : ($user->isCompany() ? $user->id : $user->parent_id);

        if (!$companyId) {
            $this->createError = 'El usuario master no puede crear reservas sin contexto de empresa.';
            return;
        }

        try {
            $bookingService->createBooking([
                'rental_space_id'           => $this->createSpaceId,
                'rental_duration_option_id' => $this->createDurationOptionId ?: null,
                'client_id'                 => $this->createClientId ?: null,
                'client_name'               => $this->createClientName ?: null,
                'client_phone'              => $this->createClientPhone ?: null,
                'starts_at'                 => $this->createStartsAt,
                'duration_minutes'          => $this->createDurationMinutes,
                'total_amount'              => $this->createTotalAmount,
                'notes'                     => $this->createNotes ?: null,
            ], (int) $companyId);

            $this->showCreateModal = false;
            session()->flash('ok', 'Reserva creada.');
        } catch (\InvalidArgumentException $e) {
            $this->createError = $e->getMessage();
        }
    }

    public function openDetailModal(int $bookingId): void
    {
        $this->detailBookingId = $bookingId;
        $this->showDetailModal = true;
    }

    public function confirmBooking(int $bookingId, BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($bookingId);
        $bookingService->confirmBooking($booking, Auth::user());
        session()->flash('ok', 'Reserva confirmada.');
    }

    public function cancelBooking(int $bookingId, BookingService $bookingService): void
    {
        $booking = Booking::findOrFail($bookingId);
        $bookingService->cancelBooking($booking, Auth::user());
        $this->showDetailModal = false;
        session()->flash('ok', 'Reserva cancelada.');
    }

    public function render()
    {
        $user      = Auth::user();
        $isMaster  = method_exists($user, 'isMaster') && $user->isMaster();
        $companyId = $isMaster ? null : ($user->isCompany() ? $user->id : $user->parent_id);

        $selectedDate           = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $startOfSelectedMonth   = $selectedDate->copy()->startOfMonth();
        $endOfSelectedMonth     = $selectedDate->copy()->endOfMonth();

        // Espacios disponibles para el filtro y el formulario
        $spacesQuery = RentalSpace::with('activeDurationOptions')->active()->orderBy('name');
        if ($companyId) {
            $spacesQuery->where('company_id', $companyId);
        }
        $spaces = $spacesQuery->get();

        // Reservas del mes seleccionado
        $bookingsQuery = Booking::with(['space', 'client', 'durationOption'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('starts_at', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at');

        if ($this->filterSpaceId) {
            $bookingsQuery->where('rental_space_id', $this->filterSpaceId);
        }

        $bookings = $bookingsQuery->get()->groupBy(fn($b) => $b->starts_at->format('Y-m-d'));

        // Clientes para el formulario de creación
        $clientsQuery = Client::orderBy('name');
        if ($companyId) {
            $clientsQuery->where('user_id', $companyId);
        }
        $clients = $clientsQuery->get(['id', 'name', 'phone']);

        // Construir grilla del calendario
        $calendarDays   = [];
        $firstDay       = $startOfSelectedMonth->copy();
        $lastDay        = $endOfSelectedMonth->copy();
        $startDayOfWeek = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;

        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $calendarDays[] = null;
        }

        $currentDay = $firstDay->copy();
        while ($currentDay <= $lastDay) {
            $dateStr      = $currentDay->format('Y-m-d');
            $dayBookings  = $bookings[$dateStr] ?? collect();

            $calendarDays[] = [
                'date'          => $dateStr,
                'day'           => $currentDay->day,
                'isToday'       => $currentDay->isToday(),
                'isSelected'    => $dateStr === $this->selectedDate,
                'isPast'        => $currentDay->isPast() && !$currentDay->isToday(),
                'bookings'      => $dayBookings,
                'hasConfirmed'  => $dayBookings->contains('status', 'confirmed'),
                'hasPending'    => $dayBookings->contains('status', 'pending'),
            ];
            $currentDay->addDay();
        }

        // Reserva seleccionada para modal detalle
        $detailBooking = $this->detailBookingId
            ? Booking::with(['space', 'client', 'durationOption'])->find($this->detailBookingId)
            : null;

        // Opciones de duración del espacio seleccionado en el form
        $createDurationOptions = $this->createSpaceId
            ? RentalDurationOption::where('rental_space_id', $this->createSpaceId)
                ->active()
                ->orderBy('minutes')
                ->get()
            : collect();

        // === SLOTS DEL DÍA SELECCIONADO ===
        $selectedDayStr    = $this->selectedDate ?: now()->toDateString();
        $selectedDayCarbon = Carbon::parse($selectedDayStr);

        // Horario operativo del comercio
        $companyUser = $isMaster ? null : ($user->isCompany() ? $user : $user->parent);
        $openTime    = $companyUser?->rental_open_time  ?? '08:00';
        $closeTime   = $companyUser?->rental_close_time ?? '22:00';

        // Reservas del día seleccionado (sin filtro de espacio, para mostrar todos)
        $bookingsForDay = Booking::with(['client'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->whereDate('starts_at', $selectedDayStr)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('starts_at')
            ->get();

        $spacesForSlots = $this->filterSpaceId
            ? $spaces->where('id', $this->filterSpaceId)
            : $spaces;

        // Comparación de "pasado" usando timezone explícita para evitar diferencias
        // entre el timezone del proceso PHP y el configurado en la aplicación
        $tz      = config('app.timezone', 'UTC');
        $nowInTz = Carbon::now($tz);
        $todayStr = $nowInTz->toDateString();
        $nowTimeStr = $nowInTz->format('H:i');

        $daySlots = $spacesForSlots->map(function ($space) use ($openTime, $closeTime, $bookingsForDay, $selectedDayStr, $tz, $todayStr, $nowTimeStr) {
            $baseInterval = $space->activeDurationOptions->min('minutes') ?? 60;
            $slots        = [];
            $current      = Carbon::parse($selectedDayStr . ' ' . $openTime, $tz);
            $close        = Carbon::parse($selectedDayStr . ' ' . $closeTime, $tz);

            // Si closeTime es medianoche (00:00) o anterior a openTime, es el día siguiente
            if ($close->lte($current)) {
                $close->addDay();
            }

            while ($current < $close) {
                $slotStart = $current->copy();
                $slotEnd   = $current->copy()->addMinutes($baseInterval);

                $booking = $bookingsForDay->first(
                    fn($b) => $b->rental_space_id == $space->id
                        && $b->starts_at->lt($slotEnd)
                        && $b->ends_at->gt($slotStart)
                );

                $slotTimeStr = $slotStart->format('H:i');
                $isPast = $selectedDayStr < $todayStr
                    || ($selectedDayStr === $todayStr && $slotTimeStr < $nowTimeStr);

                $slots[] = [
                    'time'    => $slotTimeStr,
                    'booking' => $booking,
                    'is_past' => $isPast,
                ];

                $current->addMinutes($baseInterval);
            }

            return ['space' => $space, 'slots' => $slots];
        });

        return view('livewire.rentals.booking-calendar', [
            'spaces'                => $spaces,
            'calendarDays'          => $calendarDays,
            'currentMonthLabel'     => $selectedDate->locale('es')->translatedFormat('F Y'),
            'clients'               => $clients,
            'createDurationOptions' => $createDurationOptions,
            'detailBooking'         => $detailBooking,
            'daySlots'              => $daySlots,
            'selectedDate'          => $selectedDayStr,
            'selectedDateLabel'     => $selectedDayCarbon->locale('es')->translatedFormat('l d \d\e F'),
        ]);
    }
}

<?php

namespace App\Livewire\Rentals;

use App\Models\Booking;
use App\Models\Client;
use App\Models\RentalSpace;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpaceSchedule extends Component
{
    public RentalSpace $space;

    public string $selectedDate;
    public int $selectedMonth;
    public int $selectedYear;

    // 'active' = pending + confirmed, 'all', 'pending', 'confirmed', 'finished'
    public string $filterStatus = 'active';

    // Modal crear reserva
    public bool $showCreateModal = false;
    public string $createStartsAt = '';
    public ?int $createDurationOptionId = null;
    public int $createDurationMinutes = 60;
    public string $createClientName = '';
    public string $createClientPhone = '';
    public ?int $createClientId = null;
    public string $createNotes = '';
    public string $createErrorMessage = '';

    // Modal detalle/acciones
    public bool $showDetailModal = false;
    public ?int $detailBookingId = null;

    public function mount(RentalSpace $space): void
    {
        $this->space = $space->load('activeDurationOptions', 'category');

        $today = Carbon::today();
        $this->selectedDate  = $today->toDateString();
        $this->selectedMonth = (int) $today->month;
        $this->selectedYear  = (int) $today->year;
    }

    // ── Navegación de mes ──────────────────────────────────────────────────

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = (int) $date->month;
        $this->selectedYear  = (int) $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = (int) $date->month;
        $this->selectedYear  = (int) $date->year;
    }

    public function goToToday(): void
    {
        $today = Carbon::today();
        $this->selectedDate  = $today->toDateString();
        $this->selectedMonth = (int) $today->month;
        $this->selectedYear  = (int) $today->year;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        // Si el día está fuera del mes visible, navegar al mes correspondiente
        $d = Carbon::parse($date);
        $this->selectedMonth = (int) $d->month;
        $this->selectedYear  = (int) $d->year;
    }

    // ── Generación del mini-calendario ────────────────────────────────────

    public function getCalendarDays(): array
    {
        $firstDay = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $lastDay  = $firstDay->copy()->endOfMonth();
        $today    = Carbon::today()->toDateString();

        // Días con reservas en el mes (para dots)
        $daysWithBookings = Booking::where('rental_space_id', $this->space->id)
            ->whereIn('status', ['pending', 'confirmed', 'finished'])
            ->whereYear('starts_at', $this->selectedYear)
            ->whereMonth('starts_at', $this->selectedMonth)
            ->selectRaw('DATE(starts_at) as date')
            ->distinct()
            ->pluck('date')
            ->flip()
            ->all();

        // Rellenar días vacíos al inicio (Lunes = 0)
        $startDow = ($firstDay->dayOfWeek + 6) % 7; // 0=Lun, 6=Dom

        $weeks = [];
        $week  = array_fill(0, $startDow, null);

        $current = $firstDay->copy();
        while ($current->lte($lastDay)) {
            $dateStr = $current->toDateString();
            $week[]  = [
                'date'           => $dateStr,
                'dayNum'         => (int) $current->day,
                'isCurrentMonth' => true,
                'isToday'        => $dateStr === $today,
                'isSelected'     => $dateStr === $this->selectedDate,
                'hasBookings'    => isset($daysWithBookings[$dateStr]),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week    = [];
            }
            $current->addDay();
        }

        // Rellenar al final
        if (!empty($week)) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    // ── Generación de slots horarios ──────────────────────────────────────

    public function getTimeSlots(): array
    {
        $company = $this->space->company ?? Auth::user();

        $openTime  = $company->rental_open_time  ?? '08:00';
        $closeTime = $company->rental_close_time ?? '22:00';

        // Intervalo base = menor duration option activa, o 60 min si no hay ninguna
        $baseMinutes = $this->space->activeDurationOptions->min('minutes') ?? 60;
        $baseMinutes = max(15, (int) $baseMinutes);

        $dayStart  = Carbon::parse($this->selectedDate . ' ' . $openTime);
        $dayEnd    = Carbon::parse($this->selectedDate . ' ' . $closeTime);
        $nowCarbon = Carbon::now();

        // Cargar bookings del día según filtro
        $query = Booking::with(['client', 'space'])
            ->where('rental_space_id', $this->space->id)
            ->whereDate('starts_at', $this->selectedDate);

        if ($this->filterStatus === 'active') {
            $query->whereIn('status', ['pending', 'confirmed']);
        } elseif ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        } else {
            $query->whereIn('status', ['pending', 'confirmed', 'finished', 'cancelled']);
        }

        $bookings = $query->orderBy('starts_at')->get();

        $slots  = [];
        $cursor = $dayStart->copy();

        while ($cursor->lt($dayEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd   = $cursor->copy()->addMinutes($baseMinutes);

            // Buscar booking que cubra este slot (starts_at <= slotStart AND ends_at > slotStart)
            $booking = $bookings->first(function (Booking $b) use ($slotStart) {
                return $b->starts_at->lte($slotStart) && $b->ends_at->gt($slotStart);
            });

            // Evitar duplicar: si el slot ya está cubierto por una reserva que empezó antes,
            // solo mostrarlo si es el slot de inicio exacto de esa reserva
            $isBookingStart = $booking && $booking->starts_at->eq($slotStart);
            $isCovered      = $booking && !$isBookingStart;

            $slots[] = [
                'time'         => $slotStart->toTimeString('minute'),
                'time_label'   => $slotStart->format('H:i'),
                'datetime'     => $slotStart->toDateTimeString(),
                'booking'      => $booking,
                'is_start'     => $booking ? $isBookingStart : false,
                'is_covered'   => $isCovered,
                'is_past'      => $slotStart->lt($nowCarbon),
                'slot_minutes' => $baseMinutes,
            ];

            $cursor->addMinutes($baseMinutes);
        }

        // Filtrar slots cubiertos (no inicio) para no mostrar filas repetidas de la misma reserva
        return array_filter($slots, fn ($s) => !$s['is_covered']);
    }

    // ── Modales ──────────────────────────────────────────────────────────

    public function openCreateModal(string $datetime): void
    {
        $this->reset(['createClientName', 'createClientPhone', 'createClientId', 'createNotes', 'createErrorMessage']);
        $this->createStartsAt = $datetime;
        $this->createDurationOptionId = null;
        $this->createDurationMinutes = $this->space->activeDurationOptions->min('minutes') ?? 60;
        $this->showCreateModal = true;
    }

    public function updatedCreateDurationOptionId(?int $value): void
    {
        if ($value) {
            $option = $this->space->activeDurationOptions->firstWhere('id', $value);
            if ($option) {
                $this->createDurationMinutes = $option->minutes;
            }
        }
    }

    public function saveBooking(): void
    {
        $this->createErrorMessage = '';

        $this->validate([
            'createStartsAt'       => 'required|date',
            'createDurationMinutes' => 'required|integer|min:15',
            'createClientName'     => 'nullable|string|max:200',
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $companyId = $user->isCompany()
                ? (int) $user->id
                : (int) ($user->parent_id ?? $user->id);

            $data = [
                'rental_space_id'            => $this->space->id,
                'rental_duration_option_id'  => $this->createDurationOptionId,
                'starts_at'                  => $this->createStartsAt,
                'duration_minutes'           => $this->createDurationMinutes,
                'client_name'                => $this->createClientName ?: null,
                'client_phone'               => $this->createClientPhone ?: null,
                'client_id'                  => $this->createClientId ?: null,
                'notes'                      => $this->createNotes ?: null,
                'total_amount'               => 0,
            ];

            // Si hay opción seleccionada, tomar el precio
            if ($this->createDurationOptionId) {
                $option = $this->space->activeDurationOptions->firstWhere('id', $this->createDurationOptionId);
                if ($option) {
                    $data['total_amount'] = $option->price;
                }
            }

            app(BookingService::class)->createBooking($data, $companyId);

            $this->showCreateModal = false;
            $this->dispatch('booking-created');
        } catch (\InvalidArgumentException $e) {
            $this->createErrorMessage = $e->getMessage();
        }
    }

    public function openDetailModal(int $bookingId): void
    {
        $this->detailBookingId = $bookingId;
        $this->showDetailModal = true;
    }

    public function confirmBooking(int $bookingId): void
    {
        $booking = Booking::findOrFail($bookingId);
        $this->authorize('update', $booking);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        app(BookingService::class)->confirmBooking($booking, $user);

        $this->showDetailModal = false;
    }

    public function cancelBooking(int $bookingId): void
    {
        $booking = Booking::findOrFail($bookingId);
        $this->authorize('update', $booking);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        app(BookingService::class)->cancelBooking($booking, $user);

        $this->showDetailModal = false;
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render()
    {
        $calendarWeeks = $this->getCalendarDays();
        $timeSlots     = $this->getTimeSlots();

        // Cargar clientes para el select del modal
        $companyUser = Auth::user();
        $clients     = Client::forUser($companyUser)->orderBy('name')->get();

        // Detalle del booking en modal
        $detailBooking = $this->detailBookingId
            ? Booking::with(['space', 'client', 'durationOption'])->find($this->detailBookingId)
            : null;

        return view('livewire.rentals.space-schedule', compact(
            'calendarWeeks',
            'timeSlots',
            'clients',
            'detailBooking',
        ));
    }
}

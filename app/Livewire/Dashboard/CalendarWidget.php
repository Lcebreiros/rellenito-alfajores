<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ThirdPartyService;
use App\Models\SupplyPurchase;
use Carbon\Carbon;

class CalendarWidget extends Component
{
    public int $selectedMonth;
    public int $selectedYear;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
    }

    public function goToToday()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function render()
    {
        $user = Auth::user();

        // Para el calendario modal - mes seleccionado
        $selectedDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $startOfSelectedMonth = $selectedDate->copy()->startOfMonth();
        $endOfSelectedMonth = $selectedDate->copy()->endOfMonth();

        // Para el widget - siempre mes actual
        $today = now();
        $startOfCurrentMonth = $today->copy()->startOfMonth();
        $endOfCurrentMonth = $today->copy()->endOfMonth();

        // Obtener servicios con próximos pagos (para widget - mes actual y futuros)
        $currentMonthPayments = ThirdPartyService::query()
            ->where('is_active', true)
            ->whereNotNull('next_payment_date')
            ->where('next_payment_date', '>=', $startOfCurrentMonth)
            ->orderBy('next_payment_date', 'asc')
            ->get();

        // Obtener compras de insumos del mes actual (para widget)
        $currentMonthPurchases = SupplyPurchase::query()
            ->with(['supply:id,name,base_unit'])
            ->select(['id','supply_id','created_at','total_cost','qty'])
            ->whereBetween('created_at', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->orderBy('created_at', 'desc')
            ->get();

        // Para el calendario modal - obtener eventos del mes seleccionado
        $selectedMonthPayments = ThirdPartyService::query()
            ->select(['id','service_name','provider_name','cost','next_payment_date','is_active'])
            ->where('is_active', true)
            ->whereNotNull('next_payment_date')
            ->whereBetween('next_payment_date', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->orderBy('next_payment_date', 'asc')
            ->get();

        $selectedMonthPurchases = SupplyPurchase::query()
            ->with(['supply:id,name,base_unit'])
            ->select(['id','supply_id','created_at','total_cost','qty'])
            ->whereBetween('created_at', [$startOfSelectedMonth, $endOfSelectedMonth])
            ->orderBy('created_at', 'desc')
            ->get();

        // Eventos para el widget (mes actual y futuros)
        $widgetEvents = [];
        foreach ($currentMonthPayments as $payment) {
            $date = Carbon::parse($payment->next_payment_date)->format('Y-m-d');
            if (!isset($widgetEvents[$date])) {
                $widgetEvents[$date] = [];
            }
            $widgetEvents[$date][] = [
                'type' => 'payment',
                'title' => $payment->service_name,
                'amount' => $payment->cost,
                'provider' => $payment->provider_name,
                'is_overdue' => Carbon::parse($payment->next_payment_date)->isPast(),
            ];
        }

        foreach ($currentMonthPurchases as $purchase) {
            $date = Carbon::parse($purchase->created_at)->format('Y-m-d');
            if (!isset($widgetEvents[$date])) {
                $widgetEvents[$date] = [];
            }
            $widgetEvents[$date][] = [
                'type' => 'purchase',
                'title' => $purchase->supply->name ?? 'Insumo',
                'amount' => $purchase->total_cost,
                'quantity' => $purchase->qty,
                'unit' => $purchase->supply->base_unit ?? '',
            ];
        }

        // Obtener próximos 5 eventos para el widget
        $todayStr = now()->format('Y-m-d');
        $upcomingEvents = collect($widgetEvents)
            ->flatMap(function ($dayEvents, $date) {
                return collect($dayEvents)->map(function ($event) use ($date) {
                    $event['date'] = $date;
                    return $event;
                });
            })
            ->filter(fn($event) => $event['date'] >= $todayStr)
            ->sortBy('date')
            ->take(5)
            ->values();

        // Eventos para el calendario modal (mes seleccionado)
        $calendarEvents = [];
        foreach ($selectedMonthPayments as $payment) {
            $date = Carbon::parse($payment->next_payment_date)->format('Y-m-d');
            if (!isset($calendarEvents[$date])) {
                $calendarEvents[$date] = [];
            }
            $calendarEvents[$date][] = [
                'type' => 'payment',
                'title' => $payment->service_name,
                'amount' => $payment->cost,
                'provider' => $payment->provider_name,
                'is_overdue' => Carbon::parse($payment->next_payment_date)->isPast(),
            ];
        }

        foreach ($selectedMonthPurchases as $purchase) {
            $date = Carbon::parse($purchase->created_at)->format('Y-m-d');
            if (!isset($calendarEvents[$date])) {
                $calendarEvents[$date] = [];
            }
            $calendarEvents[$date][] = [
                'type' => 'purchase',
                'title' => $purchase->supply->name ?? 'Insumo',
                'amount' => $purchase->total_cost,
                'quantity' => $purchase->qty,
                'unit' => $purchase->supply->base_unit ?? '',
            ];
        }

        // Generar calendario mensual
        $calendarDays = [];
        $firstDay = $startOfSelectedMonth->copy();
        $lastDay = $endOfSelectedMonth->copy();

        // Obtener el día de la semana del primer día (0 = domingo, 1 = lunes, etc)
        // Ajustar para que lunes sea 0
        $startDayOfWeek = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;

        // Agregar días vacíos al inicio
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $calendarDays[] = null;
        }

        // Agregar todos los días del mes
        $currentDay = $firstDay->copy();
        while ($currentDay <= $lastDay) {
            $dateStr = $currentDay->format('Y-m-d');
            $calendarDays[] = [
                'date' => $dateStr,
                'day' => $currentDay->day,
                'isToday' => $currentDay->isToday(),
                'events' => $calendarEvents[$dateStr] ?? [],
                'hasPayment' => isset($calendarEvents[$dateStr]) && collect($calendarEvents[$dateStr])->contains('type', 'payment'),
                'hasPurchase' => isset($calendarEvents[$dateStr]) && collect($calendarEvents[$dateStr])->contains('type', 'purchase'),
                'hasOverdue' => isset($calendarEvents[$dateStr]) && collect($calendarEvents[$dateStr])->contains('is_overdue', true),
            ];
            $currentDay->addDay();
        }

        return view('livewire.dashboard.calendar-widget', [
            'upcomingEvents' => $upcomingEvents,
            'currentMonth' => $selectedDate->format('F Y'),
            'totalPaymentsDue' => $currentMonthPayments->where('next_payment_date', '<=', $today)->count(),
            'totalPurchases' => $currentMonthPurchases->count(),
            'calendarDays' => $calendarDays,
        ]);
    }
}

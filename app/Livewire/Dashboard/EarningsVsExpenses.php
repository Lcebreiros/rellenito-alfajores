<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class EarningsVsExpenses extends Component
{
    public array $labels = [];
    public array $earnings = [];  // Ganancias por día (orders)
    public array $expenses = [];  // Gastos por día (costings.batch_total)

    public float $totalEarnings = 0.0;
    public float $totalExpenses = 0.0;

    public function mount(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            // Invitado: no mostramos nada (o podrías setear arrays vacíos)
            return;
        }

        $start = now()->subDays(29)->startOfDay();
        $end   = now()->endOfDay();

        // ====== GANANCIAS (Orders) ======
        $ordersByDay = collect();
        if (Schema::hasTable('orders')) {
            $q = DB::table('orders')
                ->selectRaw('DATE(created_at) as d, SUM(COALESCE(total,0)) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('d');

            // Filtrar por usuario si la columna existe
            if (Schema::hasColumn('orders', 'user_id')) {
                $q->where('user_id', $userId);
            }

            // Estados “cerrados” (ajustá a lo que uses en tu modelo Order)
            if (Schema::hasColumn('orders', 'status')) {
                $q->whereIn('status', ['paid','completed','closed','finalized']);
                // o bien: $q->where('status', \App\Models\Order::STATUS_COMPLETED);
            }

            $ordersByDay = $q->pluck('total', 'd');
        }

        // ====== GASTOS (Costings) ======
        $expensesByDay = collect();
        if (Schema::hasTable('costings')) {
            $q = DB::table('costings')
                ->selectRaw('DATE(created_at) as d, SUM(COALESCE(batch_total,0)) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('d');

            if (Schema::hasColumn('costings', 'user_id')) {
                $q->where('user_id', $userId);
            }

            $expensesByDay = $q->pluck('total', 'd');
        }

        // ====== Construcción de series día a día ======
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $this->labels[]   = $cursor->isoFormat('DD/MM');
            $this->earnings[] = (float) ($ordersByDay[$key]   ?? 0);
            $this->expenses[] = (float) ($expensesByDay[$key] ?? 0);
            $cursor->addDay();
        }

        $this->totalEarnings = array_sum($this->earnings);
        $this->totalExpenses = array_sum($this->expenses);
    }

    public function render()
    {
        return view('livewire.dashboard.earnings-vs-expenses');
    }
}

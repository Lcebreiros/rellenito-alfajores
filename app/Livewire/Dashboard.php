<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessInsight;
use App\Models\Order;
use App\Services\HealthReportService;

class Dashboard extends Component
{
    public function getCriticalAlertsProperty()
    {
        try {
            $userId = Auth::id();
            return Cache::remember("dash_alerts_{$userId}", 300, fn () =>
                BusinessInsight::forUser($userId)
                    ->active()
                    ->ofPriority('critical')
                    ->limit(3)
                    ->get()
            );
        } catch (\Throwable) {
            return collect();
        }
    }

    public function getHealthScoreProperty(): array
    {
        $userId = Auth::id();
        return Cache::remember("dash_health_{$userId}", 900, function () {
            try {
                $report = (new HealthReportService(Auth::user()))->generate();
                return [
                    'score'  => $report['overall_score'],
                    'status' => $report['status'],
                    'color'  => $report['status_color'],
                ];
            } catch (\Throwable) {
                return ['score' => null, 'status' => 'Sin datos', 'color' => '#6b7280'];
            }
        });
    }

    public function getQuickStatsProperty(): array
    {
        $default = [
            'revenue' => ['value' => 0, 'change' => null],
            'costs'   => ['value' => 0, 'change' => null],
            'profit'  => ['value' => 0, 'change' => null],
        ];

        try {
            $userId = Auth::id();
            return Cache::remember("dash_stats_{$userId}", 900, function () use ($default) {
                try {
                    $user = Auth::user();
                    $now  = now();
                    $from = $now->copy()->subDays(30)->startOfDay();
                    $prev = $now->copy()->subDays(60)->startOfDay();

                    $orders = fn ($f, $t) => Order::availableFor($user)
                        ->completed()
                        ->whereNotNull('sold_at')
                        ->where('sold_at', '>=', $f)
                        ->where('sold_at', '<', $t);

                    $rev30   = (float) $orders($from, $now)->sum('total');
                    $revPrev = (float) $orders($prev, $from)->sum('total');

                    $ids30   = $orders($from, $now)->pluck('id');
                    $idsPrev = $orders($prev, $from)->pluck('id');

                    $costQry = fn ($ids) => (float) DB::table('order_items as oi')
                        ->join('products as p', 'p.id', '=', 'oi.product_id')
                        ->whereIn('oi.order_id', $ids)
                        ->selectRaw('COALESCE(SUM(oi.quantity * COALESCE(p.cost_price, 0)), 0) as c')
                        ->value('c');

                    $cost30   = $costQry($ids30);
                    $costPrev = $costQry($idsPrev);

                    $profit30   = $rev30   - $cost30;
                    $profitPrev = $revPrev - $costPrev;

                    $change = fn ($curr, $prev) => $prev > 0
                        ? round(($curr - $prev) / $prev * 100, 1)
                        : null;

                    return [
                        'revenue' => ['value' => $rev30,    'change' => $change($rev30,    $revPrev)],
                        'costs'   => ['value' => $cost30,   'change' => $change($cost30,   $costPrev)],
                        'profit'  => ['value' => $profit30, 'change' => $change($profit30, $profitPrev)],
                    ];
                } catch (\Throwable) {
                    return $default;
                }
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'quickStats'     => $this->quickStats,
            'healthScore'    => $this->healthScore,
            'criticalAlerts' => $this->criticalAlerts,
        ]);
    }
}

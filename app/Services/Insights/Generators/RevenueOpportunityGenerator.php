<?php

namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;
use Carbon\Carbon;

/**
 * Generador de insights de oportunidades de ingreso
 *
 * Identifica productos top y patrones de venta para maximizar ingresos
 */
class RevenueOpportunityGenerator extends BaseInsightGenerator
{
    protected function getType(): string
    {
        return BusinessInsight::TYPE_REVENUE_OPPORTUNITY;
    }

    protected function shouldRun(): bool
    {
        return true;
    }

    protected function fetchData(): mixed
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        return [
            'current_month_orders' => $this->getUserOrders($currentMonth)
                ->where('status', '!=', 'cancelled'),
            'last_month_orders' => $this->getUserOrders($lastMonth, $lastMonthEnd)
                ->where('status', '!=', 'cancelled'),
        ];
    }

    protected function analyze(mixed $data): array
    {
        $insights = [];
        $currentOrders = $data['current_month_orders'];
        $lastOrders = $data['last_month_orders'];

        // Calcular ingresos totales
        $currentRevenue = $currentOrders->sum('total_price');
        $lastRevenue = $lastOrders->sum('total_price');

        // Calcular crecimiento
        if ($lastRevenue > 0) {
            $growthPercent = (($currentRevenue - $lastRevenue) / $lastRevenue) * 100;

            if ($growthPercent > 20) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_LOW,
                    "Crecimiento en ventas",
                    "Tus ventas aumentaron un " . round($growthPercent, 1) . "% este mes. ¡Sigue así!",
                    [
                        'current_revenue' => $currentRevenue,
                        'last_revenue' => $lastRevenue,
                        'growth_percent' => round($growthPercent, 2),
                    ],
                    'Ver estadísticas',
                    '/dashboard',
                    72
                );
            } elseif ($growthPercent < -10) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_MEDIUM,
                    "Baja en ventas",
                    "Tus ventas disminuyeron un " . abs(round($growthPercent, 1)) . "% este mes. Considera revisar tus estrategias.",
                    [
                        'current_revenue' => $currentRevenue,
                        'last_revenue' => $lastRevenue,
                        'growth_percent' => round($growthPercent, 2),
                    ],
                    'Ver análisis',
                    '/dashboard',
                    48
                );
            }
        }

        // Analizar productos más vendidos
        $productSales = [];
        foreach ($currentOrders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'product' => $item->product,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $productSales[$productId]['quantity'] += $item->quantity;
                $productSales[$productId]['revenue'] += $item->subtotal;
            }
        }

        // Ordenar por ingresos
        usort($productSales, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // Top 3 productos más rentables
        if (count($productSales) >= 3) {
            $top3 = array_slice($productSales, 0, 3);
            $topProducts = array_map(fn($p) => $p['product']->name, $top3);
            $totalFromTop3 = array_sum(array_column($top3, 'revenue'));

            $insights[] = $this->makeInsight(
                BusinessInsight::PRIORITY_LOW,
                "Productos con mayor ingreso",
                "Los productos " . implode(', ', $topProducts) . " generan el mayor ingreso. Asegúrate de tener stock suficiente.",
                [
                    'top_products' => $top3,
                    'total_revenue_top3' => $totalFromTop3,
                ],
                'Ver productos',
                '/products',
                72
            );
        }

        // Detectar días/horas pico
        $ordersByHour = [];
        foreach ($currentOrders as $order) {
            $hour = Carbon::parse($order->created_at)->hour;
            if (!isset($ordersByHour[$hour])) {
                $ordersByHour[$hour] = 0;
            }
            $ordersByHour[$hour]++;
        }

        if (!empty($ordersByHour)) {
            arsort($ordersByHour);
            $peakHour = array_key_first($ordersByHour);
            $peakHourFormatted = str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ':00';

            $insights[] = $this->makeInsight(
                BusinessInsight::PRIORITY_LOW,
                "Horario pico de ventas",
                "La mayoría de tus ventas ocurren alrededor de las {$peakHourFormatted}. Considera esto para planificar promociones.",
                [
                    'peak_hour' => $peakHour,
                    'orders_count' => $ordersByHour[$peakHour],
                ],
                null,
                null,
                96
            );
        }

        return $insights;
    }
}

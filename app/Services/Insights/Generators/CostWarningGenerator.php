<?php

namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;
use Carbon\Carbon;

/**
 * Generador de insights de advertencias de costos
 *
 * Identifica aumentos anormales en gastos y patrones preocupantes
 */
class CostWarningGenerator extends BaseInsightGenerator
{
    protected function getType(): string
    {
        return BusinessInsight::TYPE_COST_WARNING;
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
            'current_month_expenses' => $this->getUserExpenses($currentMonth),
            'last_month_expenses' => $this->getUserExpenses($lastMonth, $lastMonthEnd),
            'current_month_orders' => $this->getUserOrders($currentMonth)
                ->where('status', '!=', 'cancelled'),
            'last_month_orders' => $this->getUserOrders($lastMonth, $lastMonthEnd)
                ->where('status', '!=', 'cancelled'),
        ];
    }

    protected function analyze(mixed $data): array
    {
        $insights = [];

        $currentExpenses = $data['current_month_expenses'];
        $lastExpenses = $data['last_month_expenses'];
        $currentOrders = $data['current_month_orders'];
        $lastOrders = $data['last_month_orders'];

        // Calcular totales
        $currentExpenseTotal = $currentExpenses->sum(fn($e) => $this->getExpenseAmount($e));
        $lastExpenseTotal = $lastExpenses->sum(fn($e) => $this->getExpenseAmount($e));
        $currentRevenue = $currentOrders->sum('total_price');
        $lastRevenue = $lastOrders->sum('total_price');

        // Analizar aumento de gastos
        if ($lastExpenseTotal > 0) {
            $expenseGrowth = (($currentExpenseTotal - $lastExpenseTotal) / $lastExpenseTotal) * 100;

            if ($expenseGrowth > 30) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_HIGH,
                    "⚠️ Gastos en aumento",
                    "Tus gastos aumentaron un " . round($expenseGrowth, 1) . "% este mes. Revisa tus costos operativos.",
                    [
                        'current_expenses' => $currentExpenseTotal,
                        'last_expenses' => $lastExpenseTotal,
                        'growth_percent' => round($expenseGrowth, 2),
                    ],
                    'Ver gastos',
                    '/expenses',
                    48
                );
            }
        }

        // Analizar margen de ganancia
        if ($currentRevenue > 0) {
            $profitMargin = (($currentRevenue - $currentExpenseTotal) / $currentRevenue) * 100;

            if ($profitMargin < 15 && $profitMargin > 0) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_MEDIUM,
                    "💰 Margen de ganancia bajo",
                    "Tu margen de ganancia es del " . round($profitMargin, 1) . "%. Considera optimizar costos o ajustar precios.",
                    [
                        'revenue' => $currentRevenue,
                        'expenses' => $currentExpenseTotal,
                        'profit_margin' => round($profitMargin, 2),
                    ],
                    'Ver finanzas',
                    '/dashboard',
                    72
                );
            } elseif ($profitMargin <= 0) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_CRITICAL,
                    "🚨 Gastos superan ingresos",
                    "Tus gastos están superando tus ingresos este mes. Es urgente revisar tu estructura de costos.",
                    [
                        'revenue' => $currentRevenue,
                        'expenses' => $currentExpenseTotal,
                        'profit_margin' => round($profitMargin, 2),
                    ],
                    'Ver finanzas',
                    '/dashboard',
                    24
                );
            }
        }

        // Analizar gastos por tipo (ProductionExpense, ServiceExpense, SupplierExpense)
        $expensesByType = [
            'Producción' => 0,
            'Servicios' => 0,
            'Proveedores' => 0,
        ];

        foreach ($currentExpenses as $expense) {
            $amount = $this->getExpenseAmount($expense);

            if ($expense instanceof \App\Models\ProductionExpense) {
                $expensesByType['Producción'] += $amount;
            } elseif ($expense instanceof \App\Models\ServiceExpense) {
                $expensesByType['Servicios'] += $amount;
            } elseif ($expense instanceof \App\Models\SupplierExpense) {
                $expensesByType['Proveedores'] += $amount;
            }
        }

        // Filtrar categorías con gastos
        $expensesByType = array_filter($expensesByType, fn($amount) => $amount > 0);

        if (!empty($expensesByType) && $currentExpenseTotal > 0) {
            arsort($expensesByType);
            $topType = array_key_first($expensesByType);
            $topTypeAmount = $expensesByType[$topType];
            $topTypePercent = ($topTypeAmount / $currentExpenseTotal) * 100;

            if ($topTypePercent > 70 && count($expensesByType) > 1) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_MEDIUM,
                    "📊 Gastos concentrados",
                    "El " . round($topTypePercent, 1) . "% de tus gastos están en '{$topType}'. Considera diversificar.",
                    [
                        'type' => $topType,
                        'amount' => $topTypeAmount,
                        'percentage' => round($topTypePercent, 2),
                    ],
                    'Ver gastos',
                    '/expenses',
                    96
                );
            }
        }

        return $insights;
    }
}

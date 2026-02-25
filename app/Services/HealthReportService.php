<?php

namespace App\Services;

use App\Models\BusinessInsight;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calcula el Business Health Score de Nexum (0-100) por categoría.
 *
 * Ponderaciones:
 *   Revenue   35%
 *   Inventory 25%
 *   Clients   20%
 *   Costs     20%
 */
class HealthReportService
{
    public function __construct(private User $user) {}

    /**
     * Genera el reporte completo de salud del negocio.
     */
    public function generate(): array
    {
        $revenue   = $this->revenueCategory();
        $inventory = $this->inventoryCategory();
        $clients   = $this->clientsCategory();
        $costs     = $this->costsCategory();

        $overall = (int) round(
            $revenue['score']   * 0.35 +
            $inventory['score'] * 0.25 +
            $clients['score']   * 0.20 +
            $costs['score']     * 0.20
        );

        [$status, $statusColor, $statusEmoji] = $this->resolveStatus($overall);

        return [
            'overall_score'  => $overall,
            'status'         => $status,
            'status_color'   => $statusColor,
            'status_emoji'   => $statusEmoji,
            'summary'        => $this->buildSummary($overall, $revenue, $inventory, $clients, $costs),
            'categories'     => [
                'revenue'   => $revenue,
                'inventory' => $inventory,
                'clients'   => $clients,
                'costs'     => $costs,
            ],
            'generated_at'   => now()->toISOString(),
        ];
    }

    // ─── Revenue ────────────────────────────────────────────────────────────

    private function revenueCategory(): array
    {
        $now   = now();
        $start = $now->copy()->subDays(30)->startOfDay();
        $prev  = $now->copy()->subDays(60)->startOfDay();

        $currentRevenue = $this->revenueInPeriod($start, $now);
        $prevRevenue    = $this->revenueInPeriod($prev, $start);

        $orderCount = $this->ordersInPeriod($start, $now);
        $avgTicket  = $orderCount > 0 ? $currentRevenue / $orderCount : 0;

        // Sin ventas en el período actual → no puede tener score alto
        if ($currentRevenue <= 0) {
            $score        = $prevRevenue > 0 ? 10 : 20;   // cayó todo vs sin datos históricos
            $change       = $prevRevenue > 0 ? -100.0 : null;
            $displayValue = 'Sin ventas';
            $displayHint  = $prevRevenue > 0 ? 'caída desde período ant.' : 'sin actividad';
            $changeStr    = $prevRevenue > 0 ? '−100%' : '—';
        } else {
            $change = $prevRevenue > 0
                ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100
                : 100.0;   // primer período con ventas
            $score        = $this->scoreFromChange($change);
            $displayValue = ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
            $displayHint  = 'vs. 30 días ant.';
            $changeStr    = ($change >= 0 ? '+' : '') . round($change, 1) . '%';
        }

        return [
            'score'         => $score,
            'label'         => 'Ventas',
            'weight'        => 35,
            'display_value' => $displayValue,
            'display_hint'  => $displayHint,
            'icon'          => 'trending_up',
            'color'         => $this->colorFromScore($score),
            'indicators' => [
                [
                    'label'           => 'Ingresos (30 días)',
                    'value'           => $currentRevenue,
                    'value_formatted' => '$' . number_format($currentRevenue, 2, ',', '.'),
                    'what'            => 'Ingresos de los últimos 30 días',
                    'compared_to'     => '$' . number_format($prevRevenue, 2, ',', '.') . ' en los 30 días anteriores',
                    'impact'          => $change === null
                        ? 'Sin ventas registradas en este período'
                        : ($change >= 0 ? 'Crecimiento de ingresos: ' . $changeStr : 'Caída de ingresos: ' . $changeStr),
                    'action'          => $currentRevenue <= 0
                        ? 'Registrá tus primeras ventas para comenzar a medir el rendimiento'
                        : ($change !== null && $change < 0 ? 'Revisá tus estrategias de venta y oferta de productos' : 'Mantén la estrategia actual y analizá qué impulsó el crecimiento'),
                    'trend'           => $currentRevenue <= 0 ? 'down' : ($change !== null && $change >= 0 ? 'up' : 'down'),
                ],
                [
                    'label'           => 'Ticket promedio',
                    'value'           => $avgTicket,
                    'value_formatted' => '$' . number_format($avgTicket, 2, ',', '.'),
                    'what'            => 'Monto promedio por pedido completado',
                    'compared_to'     => $orderCount . ' pedidos en el período',
                    'impact'          => 'Refleja el valor de cada transacción',
                    'action'          => $avgTicket < 500
                        ? 'Considerá estrategias de upselling o combos'
                        : 'Buen ticket promedio, enfocate en aumentar frecuencia',
                    'trend'           => 'neutral',
                ],
            ],
            'metrics'    => [
                'current_revenue' => $currentRevenue,
                'prev_revenue'    => $prevRevenue,
                'change_pct'      => round($change, 1),
                'order_count'     => $orderCount,
                'avg_ticket'      => round($avgTicket, 2),
            ],
        ];
    }

    // ─── Inventory ──────────────────────────────────────────────────────────

    private function inventoryCategory(): array
    {
        $products   = Product::where('user_id', $this->user->id)->get();
        $total      = $products->count();

        if ($total === 0) {
            return $this->emptyCategory('Inventario', 'inventory_2', 50);
        }

        $outOfStock = $products->filter(fn($p) => $p->stock <= 0)->count();
        $lowStock   = $products->filter(fn($p) => $p->stock > 0 && $p->stock <= ($p->min_stock ?? 5))->count();
        $healthy    = $total - $outOfStock - $lowStock;

        $stockValue = $products->sum(fn($p) => ($p->cost_price ?? $p->price ?? 0) * max(0, $p->stock));

        $healthPct = $total > 0 ? ($healthy / $total) * 100 : 0;
        $score     = (int) round($healthPct);

        return [
            'score'         => $score,
            'label'         => 'Stock',
            'weight'        => 25,
            'display_value' => $healthy . '/' . $total,
            'display_hint'  => 'con stock ok',
            'icon'          => 'inventory_2',
            'color'         => $this->colorFromScore($score),
            'indicators' => [
                [
                    'label'           => 'Salud del stock',
                    'value'           => $score,
                    'value_formatted' => $score . '%',
                    'what'            => $healthy . ' de ' . $total . ' productos con stock saludable',
                    'compared_to'     => $outOfStock . ' sin stock · ' . $lowStock . ' en stock bajo',
                    'impact'          => $outOfStock > 0
                        ? $outOfStock . ' productos sin stock pueden generar pérdidas de ventas'
                        : 'Stock en buen estado, sin quiebres de stock',
                    'action'          => $outOfStock > 0
                        ? 'Reabastecer urgente: ' . $outOfStock . ' producto(s) sin stock'
                        : ($lowStock > 0 ? 'Planificá reposición para ' . $lowStock . ' producto(s) con stock bajo' : 'Sin acciones urgentes'),
                    'trend'           => $score >= 80 ? 'up' : ($score >= 50 ? 'neutral' : 'down'),
                ],
                [
                    'label'           => 'Valor del inventario',
                    'value'           => $stockValue,
                    'value_formatted' => '$' . number_format($stockValue, 2, ',', '.'),
                    'what'            => 'Valor total del stock disponible (a precio de costo)',
                    'compared_to'     => $total . ' productos totales',
                    'impact'          => 'Capital inmovilizado en inventario',
                    'action'          => 'Revisá la rotación de stock para optimizar el capital',
                    'trend'           => 'neutral',
                ],
            ],
            'metrics' => [
                'total'       => $total,
                'healthy'     => $healthy,
                'low_stock'   => $lowStock,
                'out_of_stock'=> $outOfStock,
                'stock_value' => round($stockValue, 2),
                'health_pct'  => round($healthPct, 1),
            ],
        ];
    }

    // ─── Clients ────────────────────────────────────────────────────────────

    private function clientsCategory(): array
    {
        $now      = now();
        $start30  = $now->copy()->subDays(30)->startOfDay();
        $start60  = $now->copy()->subDays(60)->startOfDay();

        $activeNow  = $this->activeClientsInPeriod($start30, $now);
        $activePrev = $this->activeClientsInPeriod($start60, $start30);
        $totalClients = DB::table('clients')->where('user_id', $this->user->id)->count();

        $newClients = DB::table('clients')
            ->where('user_id', $this->user->id)
            ->where('created_at', '>=', $start30)
            ->count();

        $retentionRate = $activePrev > 0 ? min(100, ($activeNow / $activePrev) * 100) : 50;
        $score = (int) round(min(100, ($activeNow > 0 ? 60 : 20) + ($retentionRate * 0.4)));

        return [
            'score'         => $score,
            'label'         => 'Clientes',
            'weight'        => 20,
            'display_value' => (string) $activeNow,
            'display_hint'  => 'activos / 30d',
            'icon'          => 'people',
            'color'         => $this->colorFromScore($score),
            'indicators' => [
                [
                    'label'           => 'Clientes activos',
                    'value'           => $activeNow,
                    'value_formatted' => (string) $activeNow,
                    'what'            => $activeNow . ' clientes compraron en los últimos 30 días',
                    'compared_to'     => $activePrev . ' en los 30 días anteriores',
                    'impact'          => $activeNow >= $activePrev
                        ? 'Base de clientes activos estable o en crecimiento'
                        : 'Disminución de clientes activos, posible señal de alerta',
                    'action'          => $activeNow < $activePrev
                        ? 'Implementá campañas de retención o comunicación con clientes inactivos'
                        : 'Enfocate en atraer nuevos clientes para sostener el crecimiento',
                    'trend'           => $activeNow >= $activePrev ? 'up' : 'down',
                ],
                [
                    'label'           => 'Clientes nuevos',
                    'value'           => $newClients,
                    'value_formatted' => (string) $newClients,
                    'what'            => $newClients . ' nuevos clientes en los últimos 30 días',
                    'compared_to'     => $totalClients . ' clientes en total',
                    'impact'          => 'Tasa de adquisición de nuevos clientes',
                    'action'          => $newClients === 0
                        ? 'Sin clientes nuevos: considerá acciones de marketing o referidos'
                        : 'Buen ritmo de adquisición, asegurate de fidelizarlos',
                    'trend'           => $newClients > 0 ? 'up' : 'neutral',
                ],
            ],
            'metrics' => [
                'active_now'     => $activeNow,
                'active_prev'    => $activePrev,
                'new_clients'    => $newClients,
                'total_clients'  => $totalClients,
                'retention_rate' => round($retentionRate, 1),
            ],
        ];
    }

    // ─── Costs ──────────────────────────────────────────────────────────────

    private function costsCategory(): array
    {
        $now   = now();
        $start = $now->copy()->subDays(30)->startOfDay();

        $revenue = $this->revenueInPeriod($start, $now);
        $costs   = $this->totalCostsInPeriod($start, $now);

        // Sin ingresos: si además hay costos → situación crítica; si no → sin datos
        if ($revenue <= 0) {
            $score        = $costs > 0 ? 10 : 50;
            $ratio        = 0.0;
            $margin       = 0.0;
            $displayValue = 'N/A';
            $displayHint  = $costs > 0 ? 'costos sin ingresos' : 'sin actividad';
        } else {
            $ratio  = ($costs / $revenue) * 100;
            $score  = match(true) {
                $ratio < 60  => 100,
                $ratio < 70  => 80,
                $ratio < 80  => 65,
                $ratio < 90  => 40,
                default      => 10,
            };
            $margin       = (($revenue - $costs) / $revenue) * 100;
            $displayValue = number_format($margin, 1) . '%';
            $displayHint  = 'margen bruto';
        }

        return [
            'score'         => $score,
            'label'         => 'Margen',
            'weight'        => 20,
            'display_value' => $displayValue,
            'display_hint'  => $displayHint,
            'icon'          => 'account_balance_wallet',
            'color'         => $this->colorFromScore($score),
            'indicators' => [
                [
                    'label'           => 'Margen bruto estimado',
                    'value'           => $margin,
                    'value_formatted' => round($margin, 1) . '%',
                    'what'            => 'Margen después de descontar costos del período',
                    'compared_to'     => 'Costos: $' . number_format($costs, 2, ',', '.') . ' de $' . number_format($revenue, 2, ',', '.') . ' en ingresos',
                    'impact'          => $margin >= 30
                        ? 'Margen saludable para operar y crecer'
                        : 'Margen bajo: los costos consumen demasiado del ingreso',
                    'action'          => $margin < 20
                        ? 'Analizá tus costos fijos y variables para reducirlos'
                        : 'Buen margen, monitoreá para mantenerlo',
                    'trend'           => $margin >= 30 ? 'up' : ($margin >= 15 ? 'neutral' : 'down'),
                ],
                [
                    'label'           => 'Ratio costos/ingresos',
                    'value'           => $ratio,
                    'value_formatted' => round($ratio, 1) . '%',
                    'what'            => 'Porcentaje de ingresos que se destinan a cubrir costos',
                    'compared_to'     => 'Referencia saludable: menos del 70%',
                    'impact'          => $ratio < 70
                        ? 'Estructura de costos eficiente'
                        : 'Los costos representan más del 70% de los ingresos',
                    'action'          => $ratio >= 80
                        ? 'Urgente: revisá y reducí gastos operativos'
                        : 'Mantené el control sobre costos variables',
                    'trend'           => $ratio < 70 ? 'up' : ($ratio < 85 ? 'neutral' : 'down'),
                ],
            ],
            'metrics' => [
                'total_costs'  => round($costs, 2),
                'revenue'      => round($revenue, 2),
                'margin_pct'   => round($margin, 1),
                'cost_ratio'   => round($ratio, 1),
            ],
        ];
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function revenueInPeriod(Carbon $from, Carbon $to): float
    {
        return (float) Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$from, $to])
            ->sum('total');
    }

    private function ordersInPeriod(Carbon $from, Carbon $to): int
    {
        return Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$from, $to])
            ->count();
    }

    private function activeClientsInPeriod(Carbon $from, Carbon $to): int
    {
        return Order::availableFor($this->user)
            ->completed()
            ->whereNotNull('client_id')
            ->whereBetween('sold_at', [$from, $to])
            ->distinct('client_id')
            ->count('client_id');
    }

    private function totalCostsInPeriod(Carbon $from, Carbon $to): float
    {
        $uid = $this->user->id;

        // Gastos de proveedores activos (fijos mensuales prorrateados)
        $supplierCosts = DB::table('supplier_expenses')
            ->where('user_id', $uid)
            ->where('is_active', true)
            ->sum('cost');

        // Gastos de servicios terceros activos
        $serviceCosts = DB::table('third_party_services')
            ->where('user_id', $uid)
            ->where('is_active', true)
            ->sum('cost');

        // Gastos de producción activos
        $productionCosts = DB::table('production_expenses')
            ->where('user_id', $uid)
            ->where('is_active', true)
            ->sum('cost_per_unit');

        return (float) ($supplierCosts + $serviceCosts + $productionCosts);
    }

    private function scoreFromChange(float $change): int
    {
        return match(true) {
            $change >= 20  => 100,
            $change >= 10  => 90,
            $change >= 0   => 75,
            $change >= -10 => 50,
            $change >= -20 => 30,
            default        => 10,
        };
    }

    private function colorFromScore(int $score): string
    {
        return match(true) {
            $score >= 80 => '#10B981', // green
            $score >= 60 => '#3B82F6', // blue
            $score >= 40 => '#F59E0B', // amber
            default      => '#EF4444', // red
        };
    }

    private function resolveStatus(int $score): array
    {
        return match(true) {
            $score >= 80 => ['Excelente',     '#10B981', '🟢'],
            $score >= 65 => ['Bueno',          '#3B82F6', '🔵'],
            $score >= 45 => ['Regular',        '#F59E0B', '🟡'],
            default      => ['Necesita atención', '#EF4444', '🔴'],
        };
    }

    private function buildSummary(int $overall, array $rev, array $inv, array $cli, array $cos): string
    {
        $weakest = collect([
            'Ventas'   => $rev['score'],
            'Stock'    => $inv['score'],
            'Clientes' => $cli['score'],
            'Margen'   => $cos['score'],
        ])->sort()->keys()->first();

        if ($overall >= 80) {
            return "Tu negocio está en excelente forma con un score de {$overall}/100. Seguí así.";
        }
        if ($overall >= 65) {
            return "Tu negocio está en buen estado ({$overall}/100). Prestá atención a: {$weakest}.";
        }
        if ($overall >= 45) {
            return "Tu negocio tiene margen de mejora ({$overall}/100). El área más débil es: {$weakest}.";
        }
        return "Tu negocio necesita atención urgente ({$overall}/100). Prioridad: {$weakest}.";
    }

    private function emptyCategory(string $label, string $icon, int $score, int $weight = 0): array
    {
        return [
            'score'         => $score,
            'label'         => $label,
            'weight'        => $weight,
            'display_value' => '—',
            'display_hint'  => 'sin datos',
            'icon'          => $icon,
            'color'         => $this->colorFromScore($score),
            'indicators'    => [],
            'metrics'       => [],
        ];
    }
}

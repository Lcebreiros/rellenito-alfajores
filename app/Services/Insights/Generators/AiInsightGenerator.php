<?php

namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\HealthReportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generador de insights impulsado por Claude AI.
 * Reemplaza los 4 generadores de reglas hardcodeadas.
 */
class AiInsightGenerator
{
    protected User $user;
    protected ?string $organizationId;

    public function __construct(User $user, ?string $organizationId = null)
    {
        $this->user           = $user;
        $this->organizationId = $organizationId;
    }

    /**
     * Retorna '*' para indicar que reemplaza todos los tipos de insight.
     */
    public function getInsightType(): string
    {
        return '*';
    }

    public function generate(): Collection
    {
        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            Log::warning('AiInsightGenerator: ANTHROPIC_API_KEY no configurada. Los usuarios Premium no recibirán diagnósticos IA.');
            throw new \RuntimeException('Nexum AI no está configurado. Contactá al administrador.');
        }

        $data     = $this->collectData();
        $previous = $this->collectPreviousInsights();
        $prompt   = $this->buildPrompt($data, $previous);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(40)->post('https://api.anthropic.com/v1/messages', [
                'model'       => 'claude-haiku-4-5-20251001',
                'max_tokens'  => 2500,
                'temperature' => 0.3,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (!$response->successful()) {
                $apiMsg = $response->json('error.message', '');
                Log::warning('AiInsightGenerator: API error', [
                    'status'  => $response->status(),
                    'message' => $apiMsg,
                ]);
                $userMsg = str_contains(strtolower($apiMsg), 'credit')
                    ? 'Sin créditos en Anthropic. Recargá en console.anthropic.com.'
                    : 'Error al conectar con Nexum AI (' . $response->status() . ').';
                throw new \RuntimeException($userMsg);
            }

            $text = $response->json('content.0.text') ?? '';
            return $this->parseInsights($text);
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AiInsightGenerator error: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar con Nexum AI.');
        }
    }

    // ─── Historial de insights previos ───────────────────────────────────

    /**
     * Trae los últimos 5 insights generados (activos o descartados) para dar
     * contexto temporal a Claude: qué se diagnosticó antes y si el usuario lo descartó.
     */
    private function collectPreviousInsights(): Collection
    {
        return BusinessInsight::forUser($this->user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['type', 'priority', 'title', 'is_dismissed', 'created_at']);
    }

    // ─── Recolección de datos ────────────────────────────────────────────

    private function collectData(): array
    {
        $now     = now();
        $start30 = $now->copy()->subDays(30)->startOfDay();
        $start60 = $now->copy()->subDays(60)->startOfDay();

        // Health score completo con categorías
        try {
            $health = (new HealthReportService($this->user))->generate();
        } catch (\Throwable) {
            $health = ['overall_score' => 0, 'status' => 'Sin datos', 'categories' => []];
        }

        // Productos con problemas de stock
        $products   = Product::where('user_id', $this->user->id)->get();
        $outOfStock = $products->filter(fn($p) => (float) $p->stock <= 0);
        $lowStock   = $products->filter(fn($p) => (float) $p->stock > 0 && (float) $p->stock <= ($p->min_stock ?? 5));

        // Ingresos + ticket promedio + cancelaciones (30d)
        $currentOrders = Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$start30, $now]);
        $currentRevenue  = (float) (clone $currentOrders)->sum('total');
        $currentCount    = (int)   (clone $currentOrders)->count();
        $avgTicket       = $currentCount > 0 ? round($currentRevenue / $currentCount, 0) : 0;

        $cancelledCount = (int) Order::availableFor($this->user)
            ->where('status', 'cancelled')
            ->whereBetween('sold_at', [$start30, $now])
            ->count();
        $totalOrders = $currentCount + $cancelledCount;
        $cancellationRate = $totalOrders > 0
            ? round($cancelledCount / $totalOrders * 100, 1)
            : 0;

        // Período anterior
        $prevRevenue = (float) Order::availableFor($this->user)
            ->completed()->whereBetween('sold_at', [$start60, $start30])->sum('total');
        $prevCount   = (int)   Order::availableFor($this->user)
            ->completed()->whereBetween('sold_at', [$start60, $start30])->count();
        $prevAvgTicket = $prevCount > 0 ? round($prevRevenue / $prevCount, 0) : 0;

        // Top 5 productos por ingresos (30d) — incluyendo costo para calcular margen
        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->where('o.user_id', $this->user->id)
            ->where('o.status', 'completed')
            ->where('o.sold_at', '>=', $start30)
            ->groupBy('oi.product_id', 'p.name', 'p.cost_price')
            ->selectRaw('p.name, SUM(oi.quantity) as units, SUM(oi.subtotal) as revenue, p.cost_price')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Top 3 productos por margen bruto (30d) — pueden diferir de top por ingreso
        $topByMargin = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->where('o.user_id', $this->user->id)
            ->where('o.status', 'completed')
            ->where('o.sold_at', '>=', $start30)
            ->whereNotNull('p.cost_price')
            ->where('p.cost_price', '>', 0)
            ->groupBy('oi.product_id', 'p.name', 'p.cost_price')
            ->selectRaw('p.name, SUM(oi.subtotal) as revenue, SUM(oi.quantity * p.cost_price) as cost')
            ->orderByRaw('(SUM(oi.subtotal) - SUM(oi.quantity * p.cost_price)) DESC')
            ->limit(3)
            ->get();

        // Clientes
        $activeNow = (int) Order::availableFor($this->user)
            ->completed()->whereNotNull('client_id')
            ->whereBetween('sold_at', [$start30, $now])
            ->distinct('client_id')->count('client_id');

        $activePrev = (int) Order::availableFor($this->user)
            ->completed()->whereNotNull('client_id')
            ->whereBetween('sold_at', [$start60, $start30])
            ->distinct('client_id')->count('client_id');

        $newClients = (int) DB::table('clients')
            ->where('user_id', $this->user->id)
            ->where('created_at', '>=', $start30)
            ->count();

        // Costos fijos activos
        $supplierCosts   = (float) DB::table('supplier_expenses')->where('user_id', $this->user->id)->where('is_active', true)->sum('cost');
        $serviceCosts    = (float) DB::table('third_party_services')->where('user_id', $this->user->id)->where('is_active', true)->sum('cost');
        $productionCosts = (float) DB::table('production_expenses')->where('user_id', $this->user->id)->where('is_active', true)->sum('cost_per_unit');
        $totalCosts      = $supplierCosts + $serviceCosts + $productionCosts;

        return compact(
            'health', 'products', 'outOfStock', 'lowStock',
            'currentRevenue', 'prevRevenue', 'currentCount', 'prevCount',
            'avgTicket', 'prevAvgTicket', 'cancellationRate',
            'topProducts', 'topByMargin',
            'activeNow', 'activePrev', 'newClients',
            'totalCosts'
        );
    }

    // ─── Construcción del prompt ─────────────────────────────────────────

    private function buildPrompt(array $d, Collection $previous): string
    {
        $health = $d['health'];
        $cats   = $health['categories'] ?? [];

        $revenueChange = $d['prevRevenue'] > 0
            ? round((($d['currentRevenue'] - $d['prevRevenue']) / $d['prevRevenue']) * 100, 1)
            : ($d['currentRevenue'] > 0 ? 100 : 0);

        $ticketChange = $d['prevAvgTicket'] > 0
            ? round((($d['avgTicket'] - $d['prevAvgTicket']) / $d['prevAvgTicket']) * 100, 1)
            : 0;

        $clientChange = $d['activePrev'] > 0
            ? round((($d['activeNow'] - $d['activePrev']) / $d['activePrev']) * 100, 1)
            : 0;

        $margin = $d['currentRevenue'] > 0
            ? round((($d['currentRevenue'] - $d['totalCosts']) / $d['currentRevenue']) * 100, 1)
            : 0;

        // Cruzar top products con stock bajo para detectar riesgo real
        $topProductNames = $d['topProducts']->pluck('name')->map('strtolower')->toArray();
        $lowStockNames   = $d['lowStock']->pluck('name')->map('strtolower')->toArray();
        $outStockNames   = $d['outOfStock']->pluck('name')->map('strtolower')->toArray();
        $topAtRisk       = array_intersect($topProductNames, array_merge($lowStockNames, $outStockNames));

        $outOfStockList = $d['outOfStock']->pluck('name')->take(8)->implode(', ');
        $lowStockList   = $d['lowStock']->pluck('name')->take(8)->implode(', ');

        $topProdStr = $d['topProducts']->map(function ($p) {
            $margin = ($p->cost_price > 0 && $p->revenue > 0)
                ? round((1 - ($p->units * $p->cost_price) / $p->revenue) * 100) . '% margen'
                : '';
            return "{$p->name} ({$p->units}u / \$" . number_format($p->revenue, 0, ',', '.') . ($margin ? " / {$margin}" : '') . ")";
        })->implode(' | ');

        $topMarginStr = $d['topByMargin']->map(function ($p) {
            $gross  = $p->revenue - $p->cost;
            $pct    = $p->revenue > 0 ? round($gross / $p->revenue * 100) : 0;
            return "{$p->name} (\${$pct}% margen bruto / \$" . number_format($gross, 0, ',', '.') . " ganancia)";
        })->implode(' | ');

        $topAtRiskStr = !empty($topAtRisk) ? implode(', ', $topAtRisk) : null;

        // Categorías del health score
        $catLines = [];
        foreach ($cats as $key => $cat) {
            $catLines[] = "  - {$cat['label']}: {$cat['score']}/100 ({$cat['display_value'] ?? ''})";
        }
        $catStr = implode("\n", $catLines);

        // Contexto histórico: diagnósticos anteriores
        $historyLines = [];
        if ($previous->isNotEmpty()) {
            $historyLines[] = "=== DIAGNÓSTICOS ANTERIORES ===";
            foreach ($previous as $p) {
                $dismissed      = $p->is_dismissed ? '[DESCARTADO por el usuario]' : '[activo]';
                $when           = $p->created_at->diffForHumans();
                $historyLines[] = "- [{$p->priority}] {$p->title} ({$when}) {$dismissed}";
            }
            $historyLines[] = "IMPORTANTE: Si un diagnóstico anterior fue DESCARTADO, no lo repitas con el mismo ángulo. Si sigue siendo relevante, abordalo desde una perspectiva diferente o con datos más actuales.";
            $historyLines[] = "Si algo mejoró desde el último análisis, mencionalo explícitamente ('a diferencia del análisis anterior...').";
            $historyLines[] = "";
        }

        $lines = array_filter(array_merge($historyLines, [
            "Eres Nexum, sistema de inteligencia de negocios para pequeñas empresas.",
            "Analizá estos datos REALES y generá diagnósticos profundos, específicos y accionables.",
            "CRUZÁ información entre áreas: si un top-seller tiene stock bajo, eso es crítico.",
            "No generés frases genéricas. Siempre nombrá productos, mostrá números exactos y explicá el impacto real.",
            "",
            "=== DATOS DEL NEGOCIO (últimos 30 días) ===",
            "Health Score: {$health['overall_score']}/100 ({$health['status']})",
            $catStr ? "Desglose por categoría:\n{$catStr}" : null,
            "Ingresos: \$" . number_format($d['currentRevenue'], 0, ',', '.') . " vs \$" . number_format($d['prevRevenue'], 0, ',', '.') . " período anterior → {$revenueChange}%",
            "Órdenes: {$d['currentCount']} completadas (prev: {$d['prevCount']}) | Ticket promedio: \$" . number_format($d['avgTicket'], 0, ',', '.') . " (prev: \$" . number_format($d['prevAvgTicket'], 0, ',', '.') . " → {$ticketChange}%)",
            "Tasa de cancelaciones: {$d['cancellationRate']}%",
            "Costos fijos: \$" . number_format($d['totalCosts'], 0, ',', '.') . " | Margen estimado: {$margin}%",
            "Clientes activos: {$d['activeNow']} ahora / {$d['activePrev']} período anterior ({$clientChange}%) | Nuevos: {$d['newClients']}",
            "Productos: {$d['products']->count()} total | {$d['outOfStock']->count()} sin stock | {$d['lowStock']->count()} en stock bajo",
            $outOfStockList ? "Sin stock: {$outOfStockList}" : null,
            $lowStockList   ? "Stock bajo: {$lowStockList}" : null,
            $topProdStr     ? "Top ventas (por ingresos): {$topProdStr}" : null,
            $topMarginStr   ? "Top por margen bruto: {$topMarginStr}" : null,
            $topAtRiskStr   ? "⚠ TOP-SELLERS EN RIESGO DE QUIEBRE: {$topAtRiskStr}" : null,
            "",
            "=== REGLAS DE ANÁLISIS ===",
            "1. Si un top-seller está sin stock o con stock bajo: diagnóstico CRÍTICO con nombre del producto y cuántos ingresos en riesgo.",
            "2. Si ingresos suben pero clientes activos bajan: analizá si es concentración de riesgo (pocos clientes comprando más).",
            "3. Si caída de ingresos: determiná si es por menos órdenes (volumen), ticket promedio más bajo, o ambos — son causas distintas.",
            "4. Si margen <15%: calculá cuánto está perdiendo y qué necesitaría subir para llegar al 20%.",
            "5. Si tasa de cancelaciones >10%: es señal de problema operativo o de producto.",
            "6. Incluí al menos un insight positivo si los datos lo justifican.",
            "7. Si hay productos con margen alto que no están en top de ingresos, es oportunidad de empuje.",
            "",
            "Reglas de prioridad:",
            "- critical: top-seller sin stock, margen negativo, caída ingresos >30%",
            "- high: stock bajo en producto con ventas activas, clientes cayeron >20%, margen <15%",
            "- medium: tendencias moderadas, oportunidades identificables con datos",
            "- low: observaciones positivas con sugerencias concretas",
            "",
            "Reglas de expires_hours (cuánto tiempo el diagnóstico sigue siendo relevante):",
            "- 8h: stock_alert crítico (el quiebre puede ocurrir hoy)",
            "- 24h: alertas de costos, clientes, cancelaciones",
            "- 48h: tendencias de ingresos o ticket promedio",
            "- 72h: predicciones y oportunidades estratégicas",
            "",
            "Generá entre 4 y 7 diagnósticos. Respondé ÚNICAMENTE con un array JSON válido. Sin texto extra, sin markdown.",
            '[{"type":"stock_alert|revenue_opportunity|cost_warning|trend|client_retention|prediction","priority":"critical|high|medium|low","title":"máx 70 chars","description":"específico con nombres y números reales, máx 280 chars","action_label":"máx 25 chars o null","metadata":{"key":"valor opcional"},"expires_hours":24}]',
        ]));

        return implode("\n", $lines);
    }

    // ─── Parseo de la respuesta JSON ─────────────────────────────────────

    private function parseInsights(string $text): Collection
    {
        // Extraer el array JSON aunque haya texto antes/después
        $start = strpos($text, '[');
        $end   = strrpos($text, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, $start, $end - $start + 1);
        }

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('AiInsightGenerator: JSON parse failed', ['text' => substr($text, 0, 600)]);
            return collect();
        }

        if (!is_array($data)) {
            return collect();
        }

        $validTypes      = ['stock_alert', 'revenue_opportunity', 'cost_warning', 'trend', 'client_retention', 'prediction', 'reminder'];
        $validPriorities = ['critical', 'high', 'medium', 'low'];

        // Mapeo de tipo → ruta de acción por defecto
        $typeRoutes = [
            'stock_alert'         => route('stock.index', [], false),
            'revenue_opportunity' => route('orders.index', [], false),
            'cost_warning'        => route('expenses.index', [], false),
            'client_retention'    => route('clients.index', [], false),
        ];

        return collect($data)
            ->filter(fn($item) =>
                is_array($item)
                && !empty($item['title'])
                && !empty($item['description'])
                && in_array($item['type'] ?? '', $validTypes)
                && in_array($item['priority'] ?? '', $validPriorities)
            )
            ->map(fn($item) => [
                'type'         => $item['type'],
                'priority'     => $item['priority'],
                'title'        => mb_substr($item['title'], 0, 100),
                'description'  => mb_substr($item['description'], 0, 600),
                'metadata'     => is_array($item['metadata'] ?? null) ? $item['metadata'] : [],
                'action_label' => !empty($item['action_label']) ? mb_substr($item['action_label'], 0, 50) : null,
                'action_route' => $typeRoutes[$item['type']] ?? null,
                'expires_at'   => now()->addHours(max(1, min(168, (int) ($item['expires_hours'] ?? 24)))),
            ])
            ->values();
    }
}

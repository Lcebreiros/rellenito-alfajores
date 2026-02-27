<?php

namespace App\Services\Insights\Generators;

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
            return collect();
        }

        $data   = $this->collectData();
        $prompt = $this->buildPrompt($data);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(35)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1800,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
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

    // ─── Recolección de datos ────────────────────────────────────────────

    private function collectData(): array
    {
        $now     = now();
        $start30 = $now->copy()->subDays(30)->startOfDay();
        $start60 = $now->copy()->subDays(60)->startOfDay();

        // Health score completo
        try {
            $health = (new HealthReportService($this->user))->generate();
        } catch (\Throwable $e) {
            $health = ['overall_score' => 0, 'status' => 'Sin datos', 'categories' => []];
        }

        // Productos con problemas de stock
        $products   = Product::where('user_id', $this->user->id)->get();
        $outOfStock = $products->filter(fn($p) => (float) $p->stock <= 0);
        $lowStock   = $products->filter(fn($p) => (float) $p->stock > 0 && (float) $p->stock <= ($p->min_stock ?? 5));

        // Ingresos comparativos
        $currentRevenue = (float) Order::availableFor($this->user)
            ->completed()->whereBetween('sold_at', [$start30, $now])->sum('total');
        $prevRevenue = (float) Order::availableFor($this->user)
            ->completed()->whereBetween('sold_at', [$start60, $start30])->sum('total');

        // Top 5 productos por ingresos (30d)
        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->where('o.user_id', $this->user->id)
            ->where('o.status', 'completed')
            ->where('o.sold_at', '>=', $start30)
            ->groupBy('oi.product_id', 'p.name')
            ->selectRaw('p.name, SUM(oi.quantity) as units, SUM(oi.subtotal) as revenue')
            ->orderByDesc('revenue')
            ->limit(5)
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
            'currentRevenue', 'prevRevenue', 'topProducts',
            'activeNow', 'activePrev', 'newClients',
            'totalCosts'
        );
    }

    // ─── Construcción del prompt ─────────────────────────────────────────

    private function buildPrompt(array $d): string
    {
        $health = $d['health'];
        $cats   = $health['categories'] ?? [];

        $revenueChange = $d['prevRevenue'] > 0
            ? round((($d['currentRevenue'] - $d['prevRevenue']) / $d['prevRevenue']) * 100, 1)
            : ($d['currentRevenue'] > 0 ? 100 : 0);

        $clientChange = $d['activePrev'] > 0
            ? round((($d['activeNow'] - $d['activePrev']) / $d['activePrev']) * 100, 1)
            : 0;

        $margin = $d['currentRevenue'] > 0
            ? round((($d['currentRevenue'] - $d['totalCosts']) / $d['currentRevenue']) * 100, 1)
            : 0;

        // Cruzar top products con stock bajo para detectar riesgo real
        $topProductNames  = $d['topProducts']->pluck('name')->map('strtolower')->toArray();
        $lowStockNames    = $d['lowStock']->pluck('name')->map('strtolower')->toArray();
        $outStockNames    = $d['outOfStock']->pluck('name')->map('strtolower')->toArray();
        $topAtRisk        = array_intersect($topProductNames, array_merge($lowStockNames, $outStockNames));

        $outOfStockList = $d['outOfStock']->pluck('name')->take(8)->implode(', ');
        $lowStockList   = $d['lowStock']->pluck('name')->take(8)->implode(', ');
        $topProdStr     = $d['topProducts']->map(fn($p) => "{$p->name} ({$p->units}u / \$" . number_format($p->revenue, 0, ',', '.') . ")")->implode(' | ');
        $topAtRiskStr   = !empty($topAtRisk) ? implode(', ', $topAtRisk) : null;

        $lines = array_filter([
            "Eres Nexum, sistema de inteligencia de negocios para pequeñas empresas.",
            "Analizá estos datos REALES y generá diagnósticos profundos, específicos y accionables.",
            "CRUZÁ información entre áreas: si un top-seller tiene stock bajo, eso es crítico.",
            "No generés frases genéricas. Siempre nombrá productos, mostrá números exactos y explicá el impacto real.",
            "",
            "=== DATOS DEL NEGOCIO (últimos 30 días) ===",
            "Health Score: {$health['overall_score']}/100 ({$health['status']})",
            "Ingresos: \$" . number_format($d['currentRevenue'], 0, ',', '.') . " vs \$" . number_format($d['prevRevenue'], 0, ',', '.') . " período anterior → {$revenueChange}%",
            "Costos fijos: \$" . number_format($d['totalCosts'], 0, ',', '.') . " | Margen estimado: {$margin}%",
            "Clientes activos: {$d['activeNow']} ahora / {$d['activePrev']} período anterior ({$clientChange}%) | Nuevos: {$d['newClients']}",
            "Productos: {$d['products']->count()} total | {$d['outOfStock']->count()} sin stock | {$d['lowStock']->count()} en stock bajo",
            $outOfStockList ? "Sin stock: {$outOfStockList}" : null,
            $lowStockList   ? "Stock bajo: {$lowStockList}" : null,
            $topProdStr     ? "Top ventas (por ingresos): {$topProdStr}" : null,
            $topAtRiskStr   ? "⚠ TOP-SELLERS EN RIESGO DE QUIEBRE: {$topAtRiskStr}" : null,
            "",
            "=== REGLAS DE ANÁLISIS ===",
            "1. Si un top-seller está sin stock o con stock bajo: diagnóstico CRÍTICO con nombre del producto y cuántos ingresos en riesgo.",
            "2. Si ingresos suben pero clientes activos bajan: analizá si es concentración de riesgo (pocos clientes comprando más).",
            "3. Si margen <15%: calculá cuánto está perdiendo y qué necesitaría subir para llegar al 20%.",
            "4. Si hay caída de ingresos >20%: identificá si es por menos clientes, menos ticket promedio, o ambos.",
            "5. Incluí al menos un insight positivo si los datos lo justifican.",
            "",
            "Reglas de prioridad:",
            "- critical: top-seller sin stock, margen negativo, caída ingresos >30%",
            "- high: stock bajo en producto con ventas activas, clientes cayeron >20%, margen <15%",
            "- medium: tendencias moderadas, oportunidades identificables con datos",
            "- low: observaciones positivas con sugerencias concretas",
            "",
            "Generá entre 4 y 7 diagnósticos. Respondé ÚNICAMENTE con un array JSON válido. Sin texto extra, sin markdown.",
            '[{"type":"stock_alert|revenue_opportunity|cost_warning|trend|client_retention|prediction","priority":"critical|high|medium|low","title":"máx 70 chars","description":"específico con nombres y números reales, máx 280 chars","action_label":"máx 25 chars o null","expires_hours":24}]',
        ]);

        return implode("\n", $lines);
    }

    // ─── Parseo de la respuesta JSON ─────────────────────────────────────

    private function parseInsights(string $text): Collection
    {
        // Extraer el array JSON aunque haya texto antes/después
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $text = $matches[0];
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
                'metadata'     => [],
                'action_label' => !empty($item['action_label']) ? mb_substr($item['action_label'], 0, 50) : null,
                'action_route' => null,
                'expires_at'   => now()->addHours(max(1, (int) ($item['expires_hours'] ?? 24))),
            ])
            ->values();
    }
}

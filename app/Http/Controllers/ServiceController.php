<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceExpense;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user() ?? auth()->user();

        $query = (method_exists($auth, 'isMaster') && $auth->isMaster())
            ? Service::query()
            : Service::availableFor($auth);

        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = trim((string) $request->input('q'));
            $lc = mb_strtolower($term, 'UTF-8');
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]);
        });

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        $services = $query->with('category')->orderBy('name')->paginate(20)->withQueryString();

        $categories = ServiceCategory::where('company_id', $auth->rootCompany()?->id ?? $auth->id)
            ->orderBy('name')
            ->get();

        return view('services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = ServiceCategory::where('company_id', $user->rootCompany()?->id ?? $user->id)
            ->orderBy('name')
            ->get();

        return view('services.create', compact('categories'));
    }

    public function show(Service $service, Request $request)
    {
        $service->load(['variants', 'category', 'supplies.supply']);

        // Período seleccionado
        $period = (int) $request->query('period', 30);
        if (!in_array($period, [30, 90, 365])) {
            $period = 30;
        }
        $from = now()->subDays($period)->startOfDay();

        // Costo desde insumos (ServiceSupply)
        $supplyCost = 0.0;
        foreach ($service->supplies as $item) {
            try {
                $factor      = UnitConverter::factorToBase($item->unit, $item->supply->base_unit);
                $baseQty     = (float) $item->qty * $factor;
                $itemCost    = $baseQty * (float) $item->supply->avg_cost_per_base;
                $waste       = (float) $item->waste_pct / 100;
                $supplyCost += $itemCost * (1 + $waste);
            } catch (\Throwable $e) {
                // unidad no convertible, se ignora
            }
        }

        // Costo desde gastos fijos (ServiceExpense)
        $expenseCost = (float) ServiceExpense::where('service_id', $service->id)
            ->where('is_active', true)
            ->sum('cost');

        $unitCost   = $supplyCost + $expenseCost;
        $hasSupplies = $service->supplies->count() > 0 || $expenseCost > 0;
        $costSource = $hasSupplies ? 'supplies' : 'none';

        // Márgenes
        $salePrice    = (float) $service->price;
        $grossMargin  = $salePrice - $unitCost;
        $marginPct    = $salePrice > 0 ? round(($grossMargin / $salePrice) * 100, 1) : 0.0;
        $marginHealth = $marginPct >= 30 ? 'green' : ($marginPct >= 15 ? 'yellow' : 'red');

        // Resumen de ventas del período
        $ordersSub = Order::query()
            ->availableFor(auth()->user())
            ->where('status', 'completed')
            ->whereNotNull('sold_at')
            ->where('sold_at', '>=', $from)
            ->select('id');

        $salesStats = DB::table('order_items as oi')
            ->whereIn('oi.order_id', $ordersSub)
            ->where('oi.service_id', $service->id)
            ->selectRaw('
                COALESCE(SUM(oi.quantity), 0)       as units_sold,
                COALESCE(SUM(oi.subtotal), 0)       as revenue,
                COALESCE(SUM(oi.quantity * ?), 0)   as cogs
            ', [$unitCost])
            ->first();

        $unitsSold   = (float) $salesStats->units_sold;
        $revenue     = (float) $salesStats->revenue;
        $cogs        = (float) $salesStats->cogs;
        $grossProfit = $revenue - $cogs;
        $hasSales    = $unitsSold > 0;

        // ── Nexum: badges comparativos (ventana fija 30 días) ──────────────
        $compareFrom  = now()->subDays(30)->startOfDay();
        $compOrdSub   = Order::query()
            ->availableFor(auth()->user())
            ->where('status', 'completed')
            ->whereNotNull('sold_at')
            ->where('sold_at', '>=', $compareFrom)
            ->select('id');

        $revByService = DB::table('order_items as oi')
            ->whereIn('oi.order_id', $compOrdSub)
            ->whereNotNull('oi.service_id')
            ->selectRaw('oi.service_id, SUM(oi.subtotal) as total_rev, SUM(oi.quantity) as total_units')
            ->groupBy('oi.service_id')
            ->get();

        $totalCompanyRev  = (float) $revByService->sum('total_rev');
        $thisServiceRow   = $revByService->firstWhere('service_id', $service->id);
        $thisServiceRev   = $thisServiceRow ? (float) $thisServiceRow->total_rev : 0.0;
        $revenueSharePct  = $totalCompanyRev > 0
            ? round(($thisServiceRev / $totalCompanyRev) * 100, 1) : 0.0;

        $sortedByUnits     = $revByService->sortByDesc('total_units')->values();
        $salesRankIdx      = $sortedByUnits->search(fn($r) => $r->service_id == $service->id);
        $salesRank         = $salesRankIdx !== false ? $salesRankIdx + 1 : null;
        $totalSoldServices = $sortedByUnits->count();

        return view('services.show', compact(
            'service',
            'salePrice', 'unitCost', 'costSource', 'grossMargin', 'marginPct', 'marginHealth',
            'unitsSold', 'revenue', 'cogs', 'grossProfit', 'hasSales', 'period',
            'revenueSharePct', 'salesRank', 'totalSoldServices',
        ));
    }

    public function nexumInsight(Service $service, Request $request)
    {
        $from = now()->subDays(30)->startOfDay();

        // Costo del servicio
        $service->load(['supplies.supply']);
        $supplyCost = 0.0;
        foreach ($service->supplies as $item) {
            try {
                $factor      = UnitConverter::factorToBase($item->unit, $item->supply->base_unit);
                $baseQty     = (float) $item->qty * $factor;
                $itemCost    = $baseQty * (float) $item->supply->avg_cost_per_base;
                $waste       = (float) $item->waste_pct / 100;
                $supplyCost += $itemCost * (1 + $waste);
            } catch (\Throwable $e) {}
        }
        $expenseCost = (float) ServiceExpense::where('service_id', $service->id)
            ->where('is_active', true)->sum('cost');
        $unitCost  = $supplyCost + $expenseCost;
        $salePrice = (float) $service->price;
        $marginPct = $salePrice > 0 ? round((($salePrice - $unitCost) / $salePrice) * 100, 1) : 0.0;

        // Ventas + badges
        $ordersSub = Order::query()
            ->availableFor(auth()->user())
            ->where('status', 'completed')
            ->whereNotNull('sold_at')
            ->where('sold_at', '>=', $from)
            ->select('id');

        $sales = DB::table('order_items as oi')
            ->whereIn('oi.order_id', $ordersSub)
            ->where('oi.service_id', $service->id)
            ->selectRaw('COALESCE(SUM(oi.quantity), 0) as units, COALESCE(SUM(oi.subtotal), 0) as rev')
            ->first();
        $unitsSold = (float) $sales->units;
        $revenue   = (float) $sales->rev;

        $revByService = DB::table('order_items as oi')
            ->whereIn('oi.order_id', $ordersSub)
            ->whereNotNull('oi.service_id')
            ->selectRaw('oi.service_id, SUM(oi.subtotal) as total_rev, SUM(oi.quantity) as total_units')
            ->groupBy('oi.service_id')
            ->get();

        $totalRev      = (float) $revByService->sum('total_rev');
        $thisRev       = (float) ($revByService->firstWhere('service_id', $service->id)?->total_rev ?? 0);
        $revenueShare  = $totalRev > 0 ? round(($thisRev / $totalRev) * 100, 1) : 0.0;
        $sortedByUnits = $revByService->sortByDesc('total_units')->values();
        $salesRankIdx  = $sortedByUnits->search(fn($r) => $r->service_id == $service->id);
        $salesRank     = $salesRankIdx !== false ? $salesRankIdx + 1 : null;
        $totalServices = $sortedByUnits->count();

        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            return response()->json(['insight' => 'Nexum Analytics requiere configuración de API.']);
        }

        $lines = array_filter([
            "Eres un asistente de análisis de negocios para pequeñas empresas. Analiza este servicio y da UNA recomendación accionable concreta en 2 oraciones, en español:",
            "",
            "Servicio: {$service->name}",
            "Precio de venta: \${$salePrice}",
            "Costo estimado: \${$unitCost}",
            "Margen neto: {$marginPct}%",
            "Unidades vendidas (30 días): {$unitsSold}",
            "Ingresos (30 días): \${$revenue}",
            $revenueShare > 0 ? "Representa el {$revenueShare}% de los ingresos de servicios" : '',
            $salesRank ? "Posición en ventas: #{$salesRank} de {$totalServices} servicios" : 'Sin ventas registradas en el período',
        ]);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 200,
                'messages'   => [['role' => 'user', 'content' => implode("\n", $lines)]],
            ]);

            if (!$response->successful()) {
                $errorType = $response->json('error.type');
                $insight = $errorType === 'invalid_request_error' && str_contains($response->json('error.message', ''), 'credit')
                    ? 'Sin crédito disponible en la cuenta Anthropic. Cargá créditos en console.anthropic.com.'
                    : 'Error al generar el análisis.';
            } else {
                $insight = $response->json('content.0.text') ?? 'Sin análisis disponible.';
            }
        } catch (\Throwable $e) {
            $insight = 'No se pudo conectar con el servicio de análisis.';
        }

        return response()->json(compact('insight'));
    }

    public function edit(Service $service)
    {
        $user = auth()->user();
        $service->load('variants', 'category');
        $categories = ServiceCategory::where('company_id', $user->rootCompany()?->id ?? $user->id)
            ->orderBy('name')
            ->get();

        return view('services.edit', compact('service', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'tags' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'new_category' => 'nullable|string|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['tags'] = $this->parseTags($request->input('tags'));

        DB::transaction(function () use ($data, $request) {
            $data['service_category_id'] = $this->resolveCategoryId($request, $request->user());
            $service = Service::create($data);
            $variants = $this->prepareVariantsPayload($request);
            if (!empty($variants)) {
                $service->variants()->createMany($variants);
            }
        });

        return redirect()->route('services.index')->with('ok', 'Servicio creado');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'tags' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'new_category' => 'nullable|string|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['tags'] = $this->parseTags($request->input('tags'));

        DB::transaction(function () use ($service, $data, $request) {
            $data['service_category_id'] = $this->resolveCategoryId($request, $request->user());
            $service->update($data);
            // Reemplazar variantes (simple y seguro)
            $service->variants()->delete();
            $variants = $this->prepareVariantsPayload($request);
            if (!empty($variants)) {
                $service->variants()->createMany($variants);
            }
        });

        return back()->with('ok', 'Servicio actualizado');
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return redirect()->route('services.index')->with('ok','Servicio eliminado');
        } catch (\Throwable $e) {
            return back()->with('error','No se pudo eliminar.');
        }
    }

    private function parseTags(?string $tags): ?array
    {
        if (!$tags) return null;
        $parts = array_filter(array_map('trim', explode(',', $tags)));
        return $parts ? array_values(array_unique($parts)) : null;
    }

    private function prepareVariantsPayload(Request $request): array
    {
        $variants = $request->input('variants', []);
        $result = [];
        foreach ($variants as $variant) {
            if (empty($variant['name'])) continue;
            $result[] = [
                'name' => $variant['name'],
                'duration_minutes' => $variant['duration_minutes'] ?? null,
                'price' => $variant['price'] ?? 0,
                'description' => $variant['description'] ?? null,
                'is_active' => !empty($variant['is_active']),
            ];
        }
        return $result;
    }

    private function resolveCategoryId(Request $request, $user): ?int
    {
        $newName = trim((string) $request->input('new_category', ''));
        if ($newName !== '') {
            $companyId = $user->rootCompany()?->id ?? $user->id;
            $category = ServiceCategory::firstOrCreate(
                ['company_id' => $companyId, 'name' => $newName],
                []
            );
            return $category->id;
        }

        return $request->input('service_category_id');
    }
}

<?php

namespace App\Services;

use App\Models\Costing;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calcula inteligencia de stock para productos e insumos.
 * Diferencia entre plan Basic (métricas base) y Premium/Enterprise (predicciones).
 */
class StockIntelligenceService
{
    private const PERIOD_DAYS        = 30;
    private const OPTIMAL_DAYS       = 14;   // lead time (7) + safety (7)
    private const DEAD_THRESHOLD     = 45;   // días sin ventas → "muerto"
    private const HIGH_ROTATION_MIN  = 2.0;  // unidades/día
    private const LOW_ROTATION_MAX   = 0.3;  // unidades/día

    // ─── API pública ─────────────────────────────────────────────────────────

    /**
     * Inteligencia de stock para un producto terminado.
     *
     * @return array{
     *   unitCost: float,
     *   dailyAvg: float,
     *   daysRemaining: int|null,
     *   stockoutDate: Carbon|null,
     *   immobilizedCapital: float,
     *   rotationLabel: string,
     *   lastSaleDate: Carbon|null,
     *   daysSinceLastSale: int|null,
     *   optimalStock: int|null,
     *   hasSales: bool,
     * }
     */
    public function forProduct(Product $product, User $user, int $period = self::PERIOD_DAYS): array
    {
        $from         = now()->subDays($period)->startOfDay();
        $currentStock = (float) ($product->stock ?? 0);

        // ── Ventas en el período ──────────────────────────────────────────────
        $orderIds = Order::availableFor($user)
            ->completed()
            ->whereNotNull('sold_at')
            ->where('sold_at', '>=', $from)
            ->select('id');

        $soldQty = (float) DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->where('product_id', $product->id)
            ->sum('quantity');

        $dailyAvg = $soldQty / $period;
        $hasSales = $soldQty > 0;

        // ── Última venta ──────────────────────────────────────────────────────
        // Búsqueda sin restricción de período para detectar productos "muertos"
        $allOrderIds = Order::availableFor($user)
            ->completed()
            ->whereNotNull('sold_at')
            ->select('id');

        $lastSaleRaw = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereIn('oi.order_id', $allOrderIds)
            ->where('oi.product_id', $product->id)
            ->max('o.sold_at');

        $lastSaleDate      = $lastSaleRaw ? Carbon::parse($lastSaleRaw) : null;
        $daysSinceLastSale = $lastSaleDate ? (int) $lastSaleDate->diffInDays(now()) : null;

        // ── Días restantes y fecha de quiebre ─────────────────────────────────
        $daysRemaining = null;
        $stockoutDate  = null;

        if ($dailyAvg > 0 && $currentStock > 0) {
            $daysRemaining = (int) floor($currentStock / $dailyAvg);
            $stockoutDate  = now()->addDays($daysRemaining)->startOfDay();
        } elseif ($currentStock <= 0) {
            $daysRemaining = 0;
        }

        // ── Capital inmovilizado ──────────────────────────────────────────────
        $unitCost          = $this->resolveUnitCost($product);
        $immobilizedCapital = $currentStock * $unitCost;

        // ── Rotación ─────────────────────────────────────────────────────────
        $rotationLabel = $this->classifyRotation($dailyAvg, $daysSinceLastSale);

        // ── Stock óptimo (Premium) ────────────────────────────────────────────
        $optimalStock = $dailyAvg > 0 ? (int) ceil($dailyAvg * self::OPTIMAL_DAYS) : null;

        return [
            'unitCost'          => $unitCost,
            'dailyAvg'          => $dailyAvg,
            'daysRemaining'     => $daysRemaining,
            'stockoutDate'      => $stockoutDate,
            'immobilizedCapital'=> $immobilizedCapital,
            'rotationLabel'     => $rotationLabel,
            'lastSaleDate'      => $lastSaleDate,
            'daysSinceLastSale' => $daysSinceLastSale,
            'optimalStock'      => $optimalStock,
            'hasSales'          => $hasSales,
        ];
    }

    /**
     * Inteligencia de stock para un insumo (supply).
     * El consumo diario se estima a partir de las recetas y las ventas de productos que los usan.
     *
     * @return array{
     *   dailyConsumption: float,
     *   daysRemaining: int|null,
     *   stockoutDate: Carbon|null,
     *   immobilizedCapital: float,
     *   rotationLabel: string,
     *   productsUsing: array,
     *   hasSales: bool,
     * }
     */
    public function forSupply(Supply $supply, User $user, int $period = self::PERIOD_DAYS): array
    {
        $from      = now()->subDays($period)->startOfDay();
        $baseStock = (float) $supply->stock_base_qty;

        // ── Recetas que usan este insumo ──────────────────────────────────────
        $recipes = ProductRecipe::with('product')
            ->where('supply_id', $supply->id)
            ->get()
            ->filter(fn($r) => $r->product !== null);

        // ── Consumo diario acumulado ──────────────────────────────────────────
        $orderIds = Order::availableFor($user)
            ->completed()
            ->whereNotNull('sold_at')
            ->where('sold_at', '>=', $from)
            ->select('id');

        $totalDailyConsumption = 0.0;
        $productsUsing         = [];
        $hasSales              = false;

        foreach ($recipes as $recipe) {
            $soldQty = (float) DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->where('product_id', $recipe->product_id)
                ->sum('quantity');

            if ($soldQty > 0) {
                $hasSales = true;
            }

            $dailySalesOfProduct = $soldQty / $period;

            // Convertir qty de receta a unidad base del insumo
            $qtyInBase = (float) $recipe->qty * $this->unitToBase($recipe->unit);

            $dailyUse = $dailySalesOfProduct * $qtyInBase;
            $totalDailyConsumption += $dailyUse;

            $productsUsing[] = [
                'name'              => $recipe->product->name,
                'qty_per_unit'      => $recipe->qty,
                'unit'              => $recipe->unit,
                'daily_sales'       => round($dailySalesOfProduct, 2),
                'daily_consumption' => round($dailyUse, 4),
            ];
        }

        // ── Días restantes ────────────────────────────────────────────────────
        $daysRemaining = null;
        $stockoutDate  = null;

        if ($totalDailyConsumption > 0 && $baseStock > 0) {
            $daysRemaining = (int) floor($baseStock / $totalDailyConsumption);
            $stockoutDate  = now()->addDays($daysRemaining)->startOfDay();
        } elseif ($baseStock <= 0) {
            $daysRemaining = 0;
        }

        // ── Capital inmovilizado ──────────────────────────────────────────────
        $immobilizedCapital = $baseStock * (float) $supply->avg_cost_per_base;

        // ── Rotación ─────────────────────────────────────────────────────────
        $rotationLabel = $this->classifyRotation($totalDailyConsumption, null);

        return [
            'dailyConsumption'   => $totalDailyConsumption,
            'daysRemaining'      => $daysRemaining,
            'stockoutDate'       => $stockoutDate,
            'immobilizedCapital' => $immobilizedCapital,
            'rotationLabel'      => $rotationLabel,
            'productsUsing'      => $productsUsing,
            'hasSales'           => $hasSales,
        ];
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Resuelve el costo unitario con prioridad:
     * 1. Último análisis de costeo guardado (Costing)
     * 2. Receta simple calculada (CostService)
     * 3. cost_price manual
     */
    private function resolveUnitCost(Product $product): float
    {
        $latest = Costing::where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->value('unit_total');

        if ($latest && (float) $latest > 0) {
            return (float) $latest;
        }

        if ($product->recipeItems()->exists()) {
            return (float) CostService::productCost($product)['unit_cost'];
        }

        return (float) ($product->cost_price ?? 0);
    }

    /**
     * Clasifica la rotación según velocidad diaria y días sin ventas.
     */
    private function classifyRotation(float $dailyRate, ?int $daysSinceLast): string
    {
        if ($dailyRate <= 0) {
            if ($daysSinceLast === null || $daysSinceLast > self::DEAD_THRESHOLD) {
                return 'dead';
            }
            return 'no_data';
        }

        if ($daysSinceLast !== null && $daysSinceLast > self::DEAD_THRESHOLD) {
            return 'dead';
        }

        if ($dailyRate >= self::HIGH_ROTATION_MIN) {
            return 'high';
        }

        if ($dailyRate >= self::LOW_ROTATION_MAX) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Factor de conversión de una unidad a su unidad base.
     * Misma tabla que SupplyController::getConversionFactor().
     */
    private function unitToBase(string $unit): float
    {
        return match ($unit) {
            'kg', 'l' => 1000.0,
            default   => 1.0,
        };
    }
}

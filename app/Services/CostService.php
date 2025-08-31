<?php
// app/Services/CostService.php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductRecipe;

class CostService {
  public static function productCost(Product $product): array {
    $total = 0.0;

    foreach ($product->recipeItems()->with('supply')->get() as $item) {
      /** @var ProductRecipe $item */
      $base = $item->supply->base_unit;                                // g/ml/u
      $factor = UnitConverter::factorToBase($item->unit, $base);       // ej kg->g
      $qtyBase = (float)$item->qty * $factor;                          // a base
      $qtyConMerma = $qtyBase * (1 + (float)$item->waste_pct/100);     // merma
      $costoBase = (float)$item->supply->avg_cost_per_base;            // $ por base
      $total += $qtyConMerma * $costoBase;
    }

    $yield = max(1, (int)($product->yield_units ?? 1)); // respeta tu products
    $unitCost = $total / $yield;

    return [
      'batch_cost' => round($total, 2),
      'unit_cost'  => round($unitCost, 4),
      'yield'      => $yield,
    ];
  }

  // margen objetivo: 60 => 0.60
  public static function suggestPrice(float $unitCost, float $targetMargin): float {
    if ($targetMargin >= 1) $targetMargin /= 100;
    return round($unitCost / (1 - $targetMargin), 2);
  }
}

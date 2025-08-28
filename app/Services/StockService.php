<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DomainException;

class StockService
{
    /**
     * @param int $qtyChange positivo suma stock, negativo descuenta
     */
    public function adjust(Product $product, int $qtyChange, string $reason = 'manual', ?Model $reference = null): Product
    {
        return DB::transaction(function () use ($product, $qtyChange, $reason, $reference) {
            $p = Product::whereKey($product->id)->lockForUpdate()->first();
            $new = $p->stock + $qtyChange;
            if ($new < 0) {
                throw new DomainException("Stock insuficiente para {$p->name}.");
            }

            $p->stock = $new;
            $p->save();

            StockAdjustment::create([
                'product_id' => $p->id,
                'quantity_change' => $qtyChange,
                'reason' => $reason,
                'reference_id' => $reference?->getKey(),
                'reference_type' => $reference ? $reference::class : null,
            ]);

            return $p;
        });
    }

    public function setAbsolute(Product $product, int $newStock, string $reason = 'manual set'): Product
    {
        $diff = $newStock - $product->stock;
        return $this->adjust($product, $diff, $reason);
    }
}

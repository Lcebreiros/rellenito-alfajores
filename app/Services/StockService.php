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
        // Bloqueo y recarga
        // Evitar el scope global 'byUser' para poder ajustar stock de productos del catálogo compartido
        $p = Product::withoutGlobalScope('byUser')
            ->whereKey($product->id)
            ->lockForUpdate()
            ->firstOrFail();

        $newStock = $p->stock + $qtyChange;
        if ($newStock < 0) {
            throw new \DomainException("Stock insuficiente para {$p->name}.");
        }

        $p->stock = $newStock;
        $p->saveQuietly(); // 🔹 Evita disparar eventos (created/updated) y así no se hace un ajuste automático

        StockAdjustment::create([
            'product_id'      => $p->id,
            'quantity_change' => $qtyChange,
            'new_stock'       => $newStock, // 🔹 usar el stock actualizado
            'reason'          => $reason,
            'reference_id'    => $reference?->getKey(),
            'reference_type'  => $reference ? $reference::class : null,
            'user_id'         => auth()->id(),
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

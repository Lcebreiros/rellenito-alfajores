<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     *
     * IMPORTANTE: Solo descuenta stock cuando el pedido se crea directamente como COMPLETED.
     * Si el pedido es DRAFT o SCHEDULED, el stock se descontará cuando cambie a COMPLETED.
     */
    public function created(OrderItem $orderItem): void
    {
        $order = $orderItem->order;

        if (!$order) {
            return;
        }

        // Solo descontar stock si el pedido se crea directamente como COMPLETED
        // (flujo OrderQuickModal con completeOnSave = true)
        if ($order->status !== OrderStatus::COMPLETED) {
            return;
        }

        $product = $orderItem->product;

        if (!$product) {
            Log::warning("OrderItemObserver: Product not found for OrderItem {$orderItem->id}");
            return;
        }

        if ($product->uses_stock) {
            // Comportamiento original: descontar stock del producto
            try {
                $product->adjustStock(
                    -$orderItem->quantity,
                    'pedido_completado',
                    auth()->user(),
                    $orderItem->order
                );

                Log::info("Stock decremented for product {$product->id}: -{$orderItem->quantity}");
            } catch (\DomainException $e) {
                Log::error("OrderItemObserver: {$e->getMessage()}");
            }
        }

        // Consumir insumos de la receta (si el producto tiene receta)
        $this->consumeRecipeSupplies($product, $orderItem->quantity);
    }

    /**
     * Handle the OrderItem "deleted" event.
     * Restore stock when an order item is deleted (only for completed orders).
     */
    public function deleted(OrderItem $orderItem): void
    {
        $order = $orderItem->order;

        if (!$order || $order->status !== OrderStatus::COMPLETED) {
            return;
        }

        $product = $orderItem->product;

        if (!$product) {
            return;
        }

        if ($product->uses_stock) {
            try {
                $product->adjustStock(
                    $orderItem->quantity,
                    'item_pedido_eliminado',
                    auth()->user(),
                    $order
                );

                Log::info("Stock restored for product {$product->id}: +{$orderItem->quantity}");
            } catch (\Exception $e) {
                Log::error("OrderItemObserver delete: {$e->getMessage()}");
            }
        }

        // Restaurar insumos de la receta
        $this->restoreRecipeSupplies($product, $orderItem->quantity);
    }

    /**
     * Consume los insumos de la receta del producto.
     *
     * Para cada ingrediente de la receta, descuenta del stock del insumo
     * la cantidad necesaria multiplicada por las unidades vendidas,
     * considerando el porcentaje de merma.
     */
    private function consumeRecipeSupplies(Product $product, float $orderQuantity): void
    {
        $recipeItems = $product->recipeItems()->with('supply')->get();

        if ($recipeItems->isEmpty()) {
            return;
        }

        foreach ($recipeItems as $recipeItem) {
            $supply = $recipeItem->supply;

            if (!$supply) {
                continue;
            }

            // Cantidad a consumir = qty por unidad * (1 + merma%) * unidades vendidas
            $wasteFactor = 1 + (($recipeItem->waste_pct ?? 0) / 100);
            $consumption = $recipeItem->qty * $wasteFactor * $orderQuantity;

            try {
                $supply->decrement('stock_base_qty', $consumption);

                Log::info("Supply {$supply->id} consumed: -{$consumption} {$supply->base_unit} for product {$product->id}");
            } catch (\Exception $e) {
                Log::error("OrderItemObserver consumeRecipeSupplies: {$e->getMessage()}");
            }
        }
    }

    /**
     * Restaura los insumos de la receta cuando se elimina un item.
     */
    private function restoreRecipeSupplies(Product $product, float $orderQuantity): void
    {
        $recipeItems = $product->recipeItems()->with('supply')->get();

        if ($recipeItems->isEmpty()) {
            return;
        }

        foreach ($recipeItems as $recipeItem) {
            $supply = $recipeItem->supply;

            if (!$supply) {
                continue;
            }

            $wasteFactor = 1 + (($recipeItem->waste_pct ?? 0) / 100);
            $restoration = $recipeItem->qty * $wasteFactor * $orderQuantity;

            try {
                $supply->increment('stock_base_qty', $restoration);

                Log::info("Supply {$supply->id} restored: +{$restoration} {$supply->base_unit} for product {$product->id}");
            } catch (\Exception $e) {
                Log::error("OrderItemObserver restoreRecipeSupplies: {$e->getMessage()}");
            }
        }
    }
}

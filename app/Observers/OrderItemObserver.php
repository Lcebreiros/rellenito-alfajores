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

        // Obtener el producto y descontar stock
        $product = $orderItem->product;

        if (!$product) {
            Log::warning("OrderItemObserver: Product not found for OrderItem {$orderItem->id}");
            return;
        }

        try {
            // Usar adjustStock del modelo Product que SÍ dispara eventos y notificaciones
            $product->adjustStock(
                -$orderItem->quantity, // negativo para descontar
                'pedido_completado',
                auth()->user(),
                $orderItem->order
            );

            Log::info("Stock decremented for product {$product->id}: -{$orderItem->quantity}");
        } catch (\DomainException $e) {
            // Si no hay stock suficiente, registrar error pero no bloquear
            Log::error("OrderItemObserver: {$e->getMessage()}");
        }
    }

    /**
     * Handle the OrderItem "deleted" event.
     * Restore stock when an order item is deleted (only for non-completed orders).
     */
    public function deleted(OrderItem $orderItem): void
    {
        $order = $orderItem->order;

        // Solo restaurar stock si la orden ya había descontado stock (completada).
        // En borradores/pedidos pendientes todavía no se descuenta, así que no hay nada que revertir.
        if (!$order || $order->status !== OrderStatus::COMPLETED) return;

        $product = $orderItem->product;

        if (!$product) {
            return;
        }

        try {
            // Restaurar stock (cantidad positiva)
            $product->adjustStock(
                $orderItem->quantity, // positivo para sumar
                'item_pedido_eliminado',
                auth()->user(),
                $order
            );

            Log::info("Stock restored for product {$product->id}: +{$orderItem->quantity}");
        } catch (\Exception $e) {
            Log::error("OrderItemObserver delete: {$e->getMessage()}");
        }
    }
}

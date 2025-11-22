<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use DomainException;

class OrderService
{
    /**
     * Devuelve el borrador actual (crea uno si no existe) y guarda el id en sesión.
     */
    public function currentDraft(Request $request): Order
    {
        $orderId = $request->session()->get('draft_order_id');
        $order   = $orderId ? Order::find($orderId) : null;

        if (!$order || $order->status !== OrderStatus::DRAFT) {
            $order = Order::create(); // por defecto status = draft
            $request->session()->put('draft_order_id', $order->id);
        }

        return $order;
    }

    /**
     * Asegura que $order pertenece al draft de esta sesión y está editable.
     */
    public function assertDraftOwnership(Request $request, Order $order): void
    {
        $draftId = (int) $request->session()->get('draft_order_id');
        if (!$draftId || $draftId !== (int)$order->id) {
            throw new UnauthorizedException('Pedido no pertenece a tu sesión.');
        }
        if ($order->status !== OrderStatus::DRAFT) {
            throw new DomainException('Pedido no editable.');
        }
    }

    /**
     * Agrega un producto (o suma cantidad si ya existe la línea).
     */
    public function addItem(int $orderId, int $productId, int $qty = 1): void
    {
        $qty = max(1, (int)$qty);

        DB::transaction(function () use ($orderId, $productId, $qty) {
            $order   = Order::lockForUpdate()->findOrFail($orderId);
            // Evitar el scope global 'byUser' para soportar inventario compartido por empresa
            $product = Product::withoutGlobalScope('byUser')->findOrFail($productId);

            // línea existente?
            $item = $order->items()->where('product_id', $product->id)->whereNull('service_id')->first();

            if ($item) {
                $item->quantity   += $qty;
                $item->unit_price  = $product->price;
                $item->subtotal    = $item->quantity * $item->unit_price;
                $item->save();
            } else {
                $order->items()->create([
                    'product_id' => $product->id,
                    'service_id' => null,
                    'quantity'   => $qty,
                    'unit_price' => $product->price,
                    'subtotal'   => $qty * $product->price,
                ]);
            }

            $order->recalcTotal();
            $order->save();
        });
    }

    /**
     * Agrega un servicio a un pedido (sin stock), o acumula cantidad si ya existe la línea.
     */
    public function addService(int $orderId, int $serviceId, int $qty = 1): void
    {
        $qty = max(1, (int)$qty);

        DB::transaction(function () use ($orderId, $serviceId, $qty) {
            $order   = Order::lockForUpdate()->findOrFail($orderId);
            $service = \App\Models\Service::findOrFail($serviceId);

            $item = $order->items()
                ->where('service_id', $service->id)
                ->whereNull('product_id')
                ->first();

            if ($item) {
                $item->quantity   += $qty;
                $item->unit_price  = $service->price;
                $item->subtotal    = $item->quantity * $item->unit_price;
                $item->save();
            } else {
                $order->items()->create([
                    'product_id' => null,
                    'service_id' => $service->id,
                    'quantity'   => $qty,
                    'unit_price' => $service->price,
                    'subtotal'   => $qty * $service->price,
                ]);
            }

            $order->recalcTotal();
            $order->save();
        });
    }

    /**
     * Incrementa o decrementa una línea en ±1. Si llega a 0, elimina.
     */
    public function mutateItem(int $orderId, int $itemId, int $delta): void
    {
        if ($delta === 0) return;

        DB::transaction(function () use ($orderId, $itemId, $delta) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            /** @var OrderItem $item */
            $item  = OrderItem::lockForUpdate()->findOrFail($itemId);

            if ((int)$item->order_id !== (int)$order->id) {
                throw new DomainException('Ítem no pertenece al pedido.');
            }

            $item->quantity += $delta;

            if ($item->quantity <= 0) {
                $item->delete();
            } else {
                $item->subtotal = $item->quantity * $item->unit_price;
                $item->save();
            }

            $order->recalcTotal();
            $order->save();
        });
    }

    /**
     * Elimina una línea específica del pedido.
     */
    public function removeItem(int $orderId, int $itemId): void
    {
        DB::transaction(function () use ($orderId, $itemId) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            if ($order->status !== OrderStatus::DRAFT) {
                throw new DomainException('Solo se pueden editar pedidos en borrador.');
            }

            // Buscar por ID de ítem y, como fallback defensivo, por product_id (por si algún caller envía product_id)
            $item = $order->items()
                ->withoutGlobalScope('byUser')
                ->where(function ($q) use ($itemId) {
                    $q->whereKey($itemId)
                      ->orWhere('product_id', $itemId);
                })
                ->lockForUpdate()
                ->first();

            if (!$item) {
                throw new DomainException('Ítem no pertenece al pedido.');
            }

            $item->delete();

            $order->recalcTotal();
            $order->save();
        });
    }

    /**
     * Ajusta la cantidad de una línea a un valor específico (<=0 elimina la línea).
     */
    public function setItemQuantity(int $orderId, int $itemId, int $qty): void
    {
        $qty = max(0, $qty);

        DB::transaction(function () use ($orderId, $itemId, $qty) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            if ($order->status !== OrderStatus::DRAFT) {
                throw new DomainException('Solo se pueden editar pedidos en borrador.');
            }

            $item = $order->items()
                ->withoutGlobalScope('byUser')
                ->where(function ($q) use ($itemId) {
                    $q->whereKey($itemId)
                      ->orWhere('product_id', $itemId);
                })
                ->lockForUpdate()
                ->first();

            if (!$item) {
                throw new DomainException('Ítem no pertenece al pedido.');
            }

            if ($qty <= 0) {
                $item->delete();
            } else {
                $item->quantity = $qty;
                $item->subtotal = $qty * $item->unit_price;
                $item->save();
            }

            $order->recalcTotal();
            $order->save();
        });
    }

    /**
     * Devuelve un snapshot listo para UI (items + total).
     * Si no pasás $orderId, toma el borrador de la sesión.
     */
    public function snapshot(?int $orderId = null): array
    {
        if (!$orderId) {
            $orderId = session('draft_order_id');
            if (!$orderId) {
                return ['items' => [], 'total' => 0.0];
            }
        }

        $order = Order::with(['items.product','items.service'])->findOrFail($orderId);

        $items = $order->items->map(function (OrderItem $i) {
            $name = $i->product?->name ?? $i->service?->name ?? 'Item';
            return [
                'id'       => (int)$i->id,
                'name'     => $name,
                'qty'      => (int)$i->quantity,
                'price'    => (float)$i->unit_price,
                'subtotal' => (float)$i->subtotal,
            ];
        })->values()->toArray();

        return [
            'id'    => (int)$order->id,
            'items' => $items,
            'total' => (float)$order->total,
        ];
    }

    /**
     * (Opcional) Validación previa de stock para un pedido.
     */
    public function validateStockSufficient(int $orderId): void
    {
        $order = Order::with(['items.product','items.service'])->findOrFail($orderId);

        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock < $item->quantity) {
                throw new DomainException("Stock insuficiente: {$item->product->name}");
            }
        }
    }
}

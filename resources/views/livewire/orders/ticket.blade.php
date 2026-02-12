{{-- resources/views/livewire/orders/ticket.blade.php --}}

@include('orders.partials.ticket', [
    'order' => $order,
    'logoUrl' => $logoUrl,
    'appName' => $appName,
    'subtotal' => $this->subtotal,
    'discount' => $this->discount,
    'tax' => $this->tax,
    'total' => $this->total,
    'paymentMethod' => $order->payment_method,
])

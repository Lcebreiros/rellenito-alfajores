<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ScheduledOrderDueToday extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'order_scheduled_today',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'subject' => 'Pedido agendado para hoy',
            'message' => '¿Realizaste el pedido #' . ($this->order->order_number ?? $this->order->id) . ' hoy?',
            'confirm_route' => route('orders.confirm-scheduled', $this->order),
            'cancel_route'  => route('orders.cancel-scheduled', $this->order),
            'url' => route('orders.show', $this->order),
        ];
    }
}


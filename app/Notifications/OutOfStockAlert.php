<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Product;

class OutOfStockAlert extends Notification
{
    use Queueable;

    public Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Agregar canal de email si el usuario lo tiene activado
        if ($notifiable->notify_by_email ?? false) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'out_of_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'message' => "Sin stock: {$this->product->name} se ha quedado sin unidades disponibles",
            'url' => route('stock.show', $this->product->id),
            'icon' => 'circle-xmark',
            'color' => 'rose',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 Alerta: Producto Sin Stock')
            ->greeting('Hola ' . $notifiable->name)
            ->line("El producto **{$this->product->name}** se ha quedado sin stock.")
            ->line("**0 unidades** disponibles")
            ->action('Ver Producto', route('stock.show', $this->product->id))
            ->line('Es necesario reabastecer este producto urgentemente.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

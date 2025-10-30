<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Product;

class LowStockAlert extends Notification
{
    use Queueable;

    public Product $product;
    public int $currentStock;
    public int $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product, int $currentStock, int $threshold)
    {
        $this->product = $product;
        $this->currentStock = $currentStock;
        $this->threshold = $threshold;
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
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
            'message' => "Stock bajo: {$this->product->name} tiene {$this->currentStock} unidades (umbral: {$this->threshold})",
            'url' => route('stock.show', $this->product->id),
            'icon' => 'triangle-exclamation',
            'color' => 'amber',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Alerta de Stock Bajo')
            ->greeting('Hola ' . $notifiable->name)
            ->line("El producto **{$this->product->name}** tiene stock bajo.")
            ->line("Stock actual: **{$this->currentStock} unidades**")
            ->line("Umbral configurado: **{$this->threshold} unidades**")
            ->action('Ver Producto', route('stock.show', $this->product->id))
            ->line('Te recomendamos reabastecer este producto pronto.');
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

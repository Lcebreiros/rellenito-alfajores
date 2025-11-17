<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use App\Events\NewNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendStockAlertNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product,
        public User $user,
        public string $type, // 'out_of_stock' or 'low_stock'
        public int $newStock,
        public int $oldStock,
        public ?int $threshold = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Recargar modelos para asegurar datos frescos
            $this->product->refresh();
            $this->user->refresh();

            if ($this->type === 'out_of_stock') {
                $this->handleOutOfStock();
            } elseif ($this->type === 'low_stock') {
                $this->handleLowStock();
            }
        } catch (\Exception $e) {
            Log::error('Stock alert notification job failed', [
                'product_id' => $this->product->id,
                'user_id' => $this->user->id,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);

            // Re-lanzar para reintentar
            throw $e;
        }
    }

    /**
     * Handle out of stock notification
     */
    protected function handleOutOfStock(): void
    {
        // Verificar que el usuario tiene la notificación habilitada
        if (!$this->user->notify_out_of_stock) {
            return;
        }

        // Enviar email solo si el usuario lo tiene activado
        if ($this->user->notify_by_email) {
            $this->user->notify(new OutOfStockAlert($this->product));
        }

        // Crear notificación en vivo (campana)
        $notification = UserNotification::create([
            'user_id' => $this->user->id,
            'type' => 'out_of_stock',
            'title' => 'Producto sin stock',
            'message' => "Sin stock: {$this->product->name} se ha quedado sin unidades",
            'data' => [
                'product_id' => $this->product->id,
                'url' => route('stock.show', $this->product->id),
                'stock' => 0,
            ],
        ]);

        // Broadcasting
        broadcast(new NewNotification($notification));
    }

    /**
     * Handle low stock notification
     */
    protected function handleLowStock(): void
    {
        // Verificar que el usuario tiene la notificación habilitada
        if (!$this->user->notify_low_stock) {
            return;
        }

        // Enviar email solo si el usuario lo tiene activado
        if ($this->user->notify_by_email) {
            $this->user->notify(new LowStockAlert($this->product, $this->newStock, $this->threshold));
        }

        // Crear notificación en vivo (campana)
        $notification = UserNotification::create([
            'user_id' => $this->user->id,
            'type' => 'low_stock',
            'title' => 'Stock bajo',
            'message' => "{$this->product->name} tiene {$this->newStock} unidades (umbral: {$this->threshold})",
            'data' => [
                'product_id' => $this->product->id,
                'url' => route('stock.show', $this->product->id),
                'stock' => $this->newStock,
                'threshold' => $this->threshold,
            ],
        ]);

        // Broadcasting
        broadcast(new NewNotification($notification));
    }
}

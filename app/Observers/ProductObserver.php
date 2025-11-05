<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Solo procesar si cambió el stock
        // Usar wasChanged() en lugar de isDirty() porque el observer se ejecuta DESPUÉS del save
        if (!$product->wasChanged('stock')) {
            return;
        }

        $newStock = (int) $product->stock;
        $oldStock = (int) $product->getOriginal('stock');

        // Obtener usuarios que deben ser notificados (owner del producto)
        $usersToNotify = $this->getUsersToNotify($product);

        foreach ($usersToNotify as $user) {
            // Verificar si el usuario tiene las notificaciones habilitadas
            if (!$user->notify_low_stock && !$user->notify_out_of_stock) {
                continue;
            }

            // Notificación de SIN STOCK (0 unidades)
            if ($user->notify_out_of_stock && $newStock === 0 && $oldStock > 0) {
                $user->notify(new OutOfStockAlert($product));

                // Notificación en vivo (campana)
                $n = UserNotification::create([
                    'user_id' => $user->id,
                    'type' => 'out_of_stock',
                    'title' => 'Producto sin stock',
                    'message' => "Sin stock: {$product->name} se ha quedado sin unidades",
                    'data' => [
                        'product_id' => $product->id,
                        'url' => route('stock.show', $product->id),
                    ],
                ]);
                broadcast(new NewNotification($n))->toOthers();
                continue; // Si está sin stock, no enviamos la de bajo stock
            }

            // Notificación de STOCK BAJO (según umbral del usuario)
            if ($user->notify_low_stock && $newStock > 0 && $newStock <= $user->low_stock_threshold) {
                // Solo notificar si pasó de arriba del umbral a abajo del umbral
                if ($oldStock > $user->low_stock_threshold) {
                    $user->notify(new LowStockAlert($product, $newStock, $user->low_stock_threshold));

                    // Notificación en vivo (campana)
                    $n = UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'low_stock',
                        'title' => 'Stock bajo',
                        'message' => "{$product->name} tiene {$newStock} unidades (umbral: {$user->low_stock_threshold})",
                        'data' => [
                            'product_id' => $product->id,
                            'url' => route('stock.show', $product->id),
                        ],
                    ]);
                    broadcast(new NewNotification($n))->toOthers();
                }
            }
        }
    }

    /**
     * Obtener usuarios que deben ser notificados sobre este producto
     */
    protected function getUsersToNotify(Product $product): \Illuminate\Support\Collection
    {
        $users = collect();

        // El propietario del producto está en user_id (no created_by_id)
        if ($product->user_id) {
            $owner = User::find($product->user_id);
            if ($owner) {
                $users->push($owner);

                // Si el propietario es una sucursal (branch), también notificar a la empresa padre
                if ($product->created_by_type === 'branch' && $owner->parent_id) {
                    $parentUser = User::find($owner->parent_id);
                    if ($parentUser) {
                        $users->push($parentUser);
                    }
                }
            }
        }

        // Si no hay user_id pero hay usuario autenticado
        if ($users->isEmpty() && auth()->check()) {
            $currentUser = auth()->user();
            $users->push($currentUser);

            // Si el usuario actual es una sucursal, notificar también a su empresa padre
            if ($currentUser->parent_id) {
                $parentUser = User::find($currentUser->parent_id);
                if ($parentUser) {
                    $users->push($parentUser);
                }
            }
        }

        // Si aún no hay usuarios, buscar usuarios con notificaciones activas
        // (para productos legacy sin propietario claro)
        if ($users->isEmpty()) {
            $activeUsers = User::where(function($query) {
                $query->where('notify_low_stock', true)
                      ->orWhere('notify_out_of_stock', true);
            })->get();

            // Si hay muy pocos usuarios, notificar a todos
            if ($activeUsers->count() <= 5) {
                $users = $activeUsers;
            } else {
                // Solo notificar al primer usuario activo (probablemente el admin principal)
                $users->push($activeUsers->first());
            }
        }

        return $users->unique('id');
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Al crear un producto, verificar si ya está en estado crítico
        $stock = (int) $product->stock;

        if ($stock === 0) {
            return; // No notificar al crear sin stock, es intencional
        }

        $usersToNotify = $this->getUsersToNotify($product);

        foreach ($usersToNotify as $user) {
            if ($user->notify_low_stock && $stock > 0 && $stock <= $user->low_stock_threshold) {
                $user->notify(new LowStockAlert($product, $stock, $user->low_stock_threshold));

                // Notificación en vivo (campana)
                $n = UserNotification::create([
                    'user_id' => $user->id,
                    'type' => 'low_stock',
                    'title' => 'Stock bajo',
                    'message' => "{$product->name} tiene {$stock} unidades (umbral: {$user->low_stock_threshold})",
                    'data' => [
                        'product_id' => $product->id,
                        'url' => route('stock.show', $product->id),
                    ],
                ]);
                broadcast(new NewNotification($n))->toOthers();
            }
        }
    }
}

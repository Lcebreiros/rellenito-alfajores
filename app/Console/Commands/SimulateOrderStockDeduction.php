<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supply;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class SimulateOrderStockDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:simulate-order {product_id} {quantity=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula un pedido y verifica el descuento de insumos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->argument('product_id');
        $quantity = $this->argument('quantity');

        $product = Product::withoutGlobalScope('byUser')->find($productId);

        if (!$product) {
            $this->error("Producto con ID {$productId} no encontrado.");
            return 1;
        }

        $this->info("=== Simulación de Pedido ===");
        $this->info("Producto: {$product->name} (ID: {$product->id})");
        $this->info("Cantidad: {$quantity}");
        $this->newLine();

        // Obtener stock actual de insumos
        $this->info("Stock de insumos ANTES del pedido:");
        $recipes = \App\Models\ProductRecipe::where('product_id', $product->id)->get();

        if ($recipes->count() === 0) {
            $this->warn("Este producto NO tiene insumos asociados.");
            return 0;
        }

        $stockBefore = [];
        foreach ($recipes as $recipe) {
            $supply = Supply::withoutGlobalScope('byUser')->find($recipe->supply_id);
            if ($supply) {
                $stockBefore[$supply->id] = $supply->stock_base_qty;
                $this->line("  - {$supply->name}: {$supply->stock_base_qty} {$supply->base_unit}");
            }
        }

        $this->newLine();

        // Obtener un usuario para crear el pedido
        $user = User::first();
        if (!$user) {
            $this->error("No hay usuarios en la base de datos.");
            return 1;
        }

        try {
            DB::beginTransaction();

            // Crear orden
            $order = Order::create([
                'user_id' => $user->id,
                'company_id' => $user->id,
                'branch_id' => $user->id,
                'status' => OrderStatus::DRAFT->value,
                'total' => $product->price * $quantity,
            ]);

            $this->info("Orden creada (ID: {$order->id})");

            // Agregar item
            $order->items()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $product->price * $quantity,
            ]);

            $this->info("Item agregado a la orden");
            $this->newLine();

            // Finalizar orden (esto debería descontar el stock)
            $this->info("Finalizando orden (descuento de stock)...");
            $order->markAsCompleted(now());

            $this->info("Orden finalizada exitosamente");
            $this->newLine();

            // Verificar stock después
            $this->info("Stock de insumos DESPUÉS del pedido:");
            foreach ($recipes as $recipe) {
                $supply = Supply::withoutGlobalScope('byUser')->find($recipe->supply_id);
                if ($supply) {
                    $supply->refresh();
                    $stockAfter = $supply->stock_base_qty;
                    $difference = $stockBefore[$supply->id] - $stockAfter;

                    $this->line("  - {$supply->name}: {$stockAfter} {$supply->base_unit}");
                    $this->line("    Diferencia: {$difference} {$supply->base_unit}");

                    if ($difference > 0) {
                        $this->info("    ✓ Stock descontado correctamente");
                    } else {
                        $this->error("    ✗ NO se descontó el stock");
                    }
                }
            }

            DB::rollBack();
            $this->newLine();
            $this->warn("Transacción revertida (no se guardaron cambios en la BD)");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error al simular pedido: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supply;
use App\Models\Service;
use App\Models\ServiceSupply;

class TestSupplyDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:supply-deduction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica las relaciones entre productos/servicios e insumos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Verificación de Relaciones Producto-Insumo ===');
        $this->newLine();

        // Verificar productos con recetas
        $products = Product::withoutGlobalScope('byUser')->get();
        $this->info("Total de productos: {$products->count()}");

        foreach ($products as $product) {
            $recipes = ProductRecipe::where('product_id', $product->id)->get();

            if ($recipes->count() > 0) {
                $this->info("\nProducto: {$product->name} (ID: {$product->id})");
                $this->info("  Recetas: {$recipes->count()}");

                foreach ($recipes as $recipe) {
                    $supply = Supply::withoutGlobalScope('byUser')->find($recipe->supply_id);
                    if ($supply) {
                        $this->line("  - Insumo: {$supply->name} (ID: {$supply->id})");
                        $this->line("    Cantidad: {$recipe->qty} {$recipe->unit}");
                        $this->line("    Desperdicio: {$recipe->waste_pct}%");
                        $this->line("    Stock actual: {$supply->stock_base_qty} {$supply->base_unit}");
                    } else {
                        $this->error("  - Insumo NO ENCONTRADO (ID: {$recipe->supply_id})");
                    }
                }
            }
        }

        $this->newLine();
        $this->info('=== Verificación de Relaciones Servicio-Insumo ===');
        $this->newLine();

        // Verificar servicios con insumos
        $services = Service::withoutGlobalScope('byUser')->get();
        $this->info("Total de servicios: {$services->count()}");

        foreach ($services as $service) {
            $serviceSupplies = ServiceSupply::where('service_id', $service->id)->get();

            if ($serviceSupplies->count() > 0) {
                $this->info("\nServicio: {$service->name} (ID: {$service->id})");
                $this->info("  Insumos: {$serviceSupplies->count()}");

                foreach ($serviceSupplies as $ss) {
                    $supply = Supply::withoutGlobalScope('byUser')->find($ss->supply_id);
                    if ($supply) {
                        $this->line("  - Insumo: {$supply->name} (ID: {$supply->id})");
                        $this->line("    Cantidad: {$ss->qty} {$ss->unit}");
                        $this->line("    Desperdicio: {$ss->waste_pct}%");
                        $this->line("    Stock actual: {$supply->stock_base_qty} {$supply->base_unit}");
                    } else {
                        $this->error("  - Insumo NO ENCONTRADO (ID: {$ss->supply_id})");
                    }
                }
            }
        }

        $this->newLine();
        $this->info('Verificación completada.');

        return 0;
    }
}

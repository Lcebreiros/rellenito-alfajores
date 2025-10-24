<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Si existe el unique viejo, dropearlo antes de construir el nuevo esquema
        if ($this->indexExists('order_items', 'order_items_order_id_product_id_unique')) {
            DB::statement('ALTER TABLE `order_items` DROP INDEX `order_items_order_id_product_id_unique`');
        }

        // Asegurar índices simples si faltaran (evita que MySQL "necesite" el unique anterior)
        if (! $this->indexExists('order_items', 'idx_order_items_order_id')) {
            try { DB::statement('CREATE INDEX `idx_order_items_order_id` ON `order_items` (`order_id`)'); } catch (\Throwable $e) { /* ignore */ }
        }
        if (! $this->indexExists('order_items', 'idx_order_items_product_id')) {
            try { DB::statement('CREATE INDEX `idx_order_items_product_id` ON `order_items` (`product_id`)'); } catch (\Throwable $e) { /* ignore */ }
        }

        Schema::table('order_items', function (Blueprint $table) {
            // 2) Hacer product_id nullable (si DB soporta change())
            if (Schema::hasColumn('order_items', 'product_id')) {
                try { $table->foreignId('product_id')->nullable()->change(); } catch (\Throwable $e) { /* ignore if dbal missing */ }
            }

            // 3) Agregar service_id
            if (!Schema::hasColumn('order_items', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('product_id')->constrained('services')->nullOnDelete();
            }

            // 4) Crear unique triple
            $table->unique(['order_id','product_id','service_id'], 'uniq_orderitem_order_prod_serv');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            try { $table->dropUnique('uniq_orderitem_order_prod_serv'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('order_items', 'service_id')) {
                $table->dropConstrainedForeignId('service_id');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                try { $table->foreignId('product_id')->nullable(false)->change(); } catch (\Throwable $e) { /* ignore if dbal missing */ }
            }
        });

        // Restaurar unique original si no existe (fuera del blueprint)
        if (! $this->indexExists('order_items', 'order_items_order_id_product_id_unique')) {
            try { DB::statement('ALTER TABLE `order_items` ADD UNIQUE `order_items_order_id_product_id_unique` (`order_id`, `product_id`)'); } catch (\Throwable $e) {}
        }

        // Limpieza de índices auxiliares (best-effort)
        try { DB::statement('DROP INDEX `idx_order_items_product_id` ON `order_items`'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX `idx_order_items_order_id` ON `order_items`'); } catch (\Throwable $e) {}
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$db, $table, $indexName]
        );
        return !empty($rows);
    }
};

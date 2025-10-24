<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 0) Asegurar que existen índices simples para FKs y soltar FKs que usen el índice único
        //    En algunos hosts MySQL/MariaDB, el FK puede "atar" el índice compuesto; hay que soltarlo antes
        $this->dropForeignIfExists('order_items', 'order_id');
        $this->dropForeignIfExists('order_items', 'product_id');

        if (! $this->indexExists('order_items', 'idx_order_items_order_id')) {
            try { DB::statement('CREATE INDEX `idx_order_items_order_id` ON `order_items` (`order_id`)'); } catch (\Throwable $e) { /* ignore */ }
        }
        if (! $this->indexExists('order_items', 'idx_order_items_product_id')) {
            try { DB::statement('CREATE INDEX `idx_order_items_product_id` ON `order_items` (`product_id`)'); } catch (\Throwable $e) { /* ignore */ }
        }

        // 1) Dropear unique viejo si existe (ya con índices alternativos presentes)
        if ($this->indexExists('order_items', 'order_items_order_id_product_id_unique')) {
            DB::statement('ALTER TABLE `order_items` DROP INDEX `order_items_order_id_product_id_unique`');
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

        // 5) Recrear FKs con los índices simples
        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            } catch (\Throwable $e) {}
            try {
                $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        // Soltar FKs para revertir con seguridad
        $this->dropForeignIfExists('order_items', 'order_id');
        $this->dropForeignIfExists('order_items', 'product_id');

        Schema::table('order_items', function (Blueprint $table) {
            try { $table->dropUnique('uniq_orderitem_order_prod_serv'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('order_items', 'service_id')) {
                $table->dropConstrainedForeignId('service_id');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                try { $table->foreignId('product_id')->nullable(false)->change(); } catch (\Throwable $e) { /* ignore if dbal missing */ }
            }
        });

        // Restaurar unique original
        if (! $this->indexExists('order_items', 'order_items_order_id_product_id_unique')) {
            try { DB::statement('ALTER TABLE `order_items` ADD UNIQUE `order_items_order_id_product_id_unique` (`order_id`, `product_id`)'); } catch (\Throwable $e) {}
        }

        // Recrear FKs originales
        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            } catch (\Throwable $e) {}
            try {
                $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            } catch (\Throwable $e) {}
        });
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

    private function dropForeignIfExists(string $table, string $column): void
    {
        $db = DB::getDatabaseName();
        $name = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');
        if ($name) {
            try { DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$name`"); } catch (\Throwable $e) {}
        }
    }
};

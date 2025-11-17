<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índice en parent_id para consultas jerárquicas
            if (!$this->indexExists('users', 'users_parent_id_index')) {
                $table->index('parent_id', 'users_parent_id_index');
            }

            // Índice en hierarchy_level para filtros por rol
            if (!$this->indexExists('users', 'users_hierarchy_level_index')) {
                $table->index('hierarchy_level', 'users_hierarchy_level_index');
            }

            // Índice compuesto para consultas jerárquicas complejas
            if (!$this->indexExists('users', 'users_hierarchy_composite_index')) {
                $table->index(['parent_id', 'hierarchy_level', 'is_active'], 'users_hierarchy_composite_index');
            }
        });

        // Crear índice en hierarchy_path usando SQL directo (para búsquedas con LIKE)
        if (!$this->indexExists('users', 'users_hierarchy_path_index') && Schema::hasColumn('users', 'hierarchy_path')) {
            DB::statement('CREATE INDEX users_hierarchy_path_index ON users(hierarchy_path(255))');
        }

        // Índices adicionales para orders
        Schema::table('orders', function (Blueprint $table) {
            // Índice en payment_status
            if (!$this->indexExists('orders', 'orders_payment_status_index') && Schema::hasColumn('orders', 'payment_status')) {
                $table->index('payment_status', 'orders_payment_status_index');
            }

            // Índice compuesto para reportes por sucursal
            if (!$this->indexExists('orders', 'orders_branch_status_date_index') && Schema::hasColumn('orders', 'sold_at')) {
                $table->index(['branch_id', 'status', 'sold_at'], 'orders_branch_status_date_index');
            }

            // Índice compuesto para reportes por empresa
            if (!$this->indexExists('orders', 'orders_company_status_date_index') && Schema::hasColumn('orders', 'sold_at')) {
                $table->index(['company_id', 'status', 'sold_at'], 'orders_company_status_date_index');
            }
        });

        // Índices para product_locations
        Schema::table('product_locations', function (Blueprint $table) {
            // Índice en branch_id para consultas "todos los productos de una sucursal"
            if (!$this->indexExists('product_locations', 'product_locations_branch_id_index')) {
                $table->index('branch_id', 'product_locations_branch_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_parent_id_index')) {
                $table->dropIndex('users_parent_id_index');
            }
            if ($this->indexExists('users', 'users_hierarchy_level_index')) {
                $table->dropIndex('users_hierarchy_level_index');
            }
            if ($this->indexExists('users', 'users_hierarchy_composite_index')) {
                $table->dropIndex('users_hierarchy_composite_index');
            }
        });

        if ($this->indexExists('users', 'users_hierarchy_path_index')) {
            DB::statement('DROP INDEX users_hierarchy_path_index ON users');
        }

        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'orders_payment_status_index')) {
                $table->dropIndex('orders_payment_status_index');
            }
            if ($this->indexExists('orders', 'orders_branch_status_date_index')) {
                $table->dropIndex('orders_branch_status_date_index');
            }
            if ($this->indexExists('orders', 'orders_company_status_date_index')) {
                $table->dropIndex('orders_company_status_date_index');
            }
        });

        Schema::table('product_locations', function (Blueprint $table) {
            if ($this->indexExists('product_locations', 'product_locations_branch_id_index')) {
                $table->dropIndex('product_locations_branch_id_index');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};

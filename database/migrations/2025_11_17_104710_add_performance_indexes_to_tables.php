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
        // Índices para tabla orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Índices individuales para filtros comunes
                if (!$this->indexExists('orders', 'orders_user_id_index')) {
                    $table->index('user_id', 'orders_user_id_index');
                }
                if (!$this->indexExists('orders', 'orders_client_id_index')) {
                    $table->index('client_id', 'orders_client_id_index');
                }
                if (!$this->indexExists('orders', 'orders_branch_id_index')) {
                    $table->index('branch_id', 'orders_branch_id_index');
                }
                if (!$this->indexExists('orders', 'orders_company_id_index')) {
                    $table->index('company_id', 'orders_company_id_index');
                }
                if (!$this->indexExists('orders', 'orders_status_index')) {
                    $table->index('status', 'orders_status_index');
                }

                // Índices compuestos para queries complejas
                if (!$this->indexExists('orders', 'orders_user_created_index')) {
                    $table->index(['user_id', 'created_at'], 'orders_user_created_index');
                }
                if (!$this->indexExists('orders', 'orders_status_created_index')) {
                    $table->index(['status', 'created_at'], 'orders_status_created_index');
                }

                // Índice para órdenes agendadas
                if (!$this->indexExists('orders', 'orders_scheduled_index')) {
                    $table->index(['is_scheduled', 'scheduled_for'], 'orders_scheduled_index');
                }
            });
        }

        // Índices para tabla products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!$this->indexExists('products', 'products_user_id_index') && Schema::hasColumn('products', 'user_id')) {
                    $table->index('user_id', 'products_user_id_index');
                }
                if (!$this->indexExists('products', 'products_company_id_index') && Schema::hasColumn('products', 'company_id')) {
                    $table->index('company_id', 'products_company_id_index');
                }
                if (!$this->indexExists('products', 'products_branch_id_index') && Schema::hasColumn('products', 'branch_id')) {
                    $table->index('branch_id', 'products_branch_id_index');
                }
                if (!$this->indexExists('products', 'products_sku_index') && Schema::hasColumn('products', 'sku')) {
                    $table->index('sku', 'products_sku_index');
                }
                if (!$this->indexExists('products', 'products_is_active_index') && Schema::hasColumn('products', 'is_active')) {
                    $table->index('is_active', 'products_is_active_index');
                }

                // Índice para búsquedas por nombre
                if (!$this->indexExists('products', 'products_name_index') && Schema::hasColumn('products', 'name')) {
                    $table->index('name', 'products_name_index');
                }
            });
        }

        // Índices para tabla clients
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (!$this->indexExists('clients', 'clients_user_id_index') && Schema::hasColumn('clients', 'user_id')) {
                    $table->index('user_id', 'clients_user_id_index');
                }
                if (!$this->indexExists('clients', 'clients_email_index') && Schema::hasColumn('clients', 'email')) {
                    $table->index('email', 'clients_email_index');
                }
                if (!$this->indexExists('clients', 'clients_phone_index') && Schema::hasColumn('clients', 'phone')) {
                    $table->index('phone', 'clients_phone_index');
                }
                if (!$this->indexExists('clients', 'clients_is_active_index') && Schema::hasColumn('clients', 'is_active')) {
                    $table->index('is_active', 'clients_is_active_index');
                }
            });
        }

        // Índices para tabla order_items
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!$this->indexExists('order_items', 'order_items_order_id_index')) {
                    $table->index('order_id', 'order_items_order_id_index');
                }
                if (!$this->indexExists('order_items', 'order_items_product_id_index')) {
                    $table->index('product_id', 'order_items_product_id_index');
                }
                if (!$this->indexExists('order_items', 'order_items_user_id_index')) {
                    $table->index('user_id', 'order_items_user_id_index');
                }
            });
        }

        // Índices para tabla supplies
        if (Schema::hasTable('supplies')) {
            Schema::table('supplies', function (Blueprint $table) {
                if (!$this->indexExists('supplies', 'supplies_user_id_index')) {
                    $table->index('user_id', 'supplies_user_id_index');
                }
                if (!$this->indexExists('supplies', 'supplies_supplier_id_index')) {
                    $table->index('supplier_id', 'supplies_supplier_id_index');
                }
                if (!$this->indexExists('supplies', 'supplies_name_index')) {
                    $table->index('name', 'supplies_name_index');
                }
            });
        }

        // Índices para tabla suppliers
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!$this->indexExists('suppliers', 'suppliers_user_id_index')) {
                    $table->index('user_id', 'suppliers_user_id_index');
                }
                if (!$this->indexExists('suppliers', 'suppliers_is_active_index')) {
                    $table->index('is_active', 'suppliers_is_active_index');
                }
            });
        }

        // Índices para tabla supplier_expenses
        if (Schema::hasTable('supplier_expenses')) {
            Schema::table('supplier_expenses', function (Blueprint $table) {
                if (!$this->indexExists('supplier_expenses', 'supplier_expenses_user_id_index')) {
                    $table->index('user_id', 'supplier_expenses_user_id_index');
                }
                if (!$this->indexExists('supplier_expenses', 'supplier_expenses_supplier_id_index')) {
                    $table->index('supplier_id', 'supplier_expenses_supplier_id_index');
                }
                if (!$this->indexExists('supplier_expenses', 'supplier_expenses_product_id_index')) {
                    $table->index('product_id', 'supplier_expenses_product_id_index');
                }
                if (!$this->indexExists('supplier_expenses', 'supplier_expenses_is_active_index')) {
                    $table->index('is_active', 'supplier_expenses_is_active_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices en orden inverso
        if (Schema::hasTable('supplier_expenses')) {
            Schema::table('supplier_expenses', function (Blueprint $table) {
                $table->dropIndex('supplier_expenses_user_id_index');
                $table->dropIndex('supplier_expenses_supplier_id_index');
                $table->dropIndex('supplier_expenses_product_id_index');
                $table->dropIndex('supplier_expenses_is_active_index');
            });
        }

        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropIndex('suppliers_user_id_index');
                $table->dropIndex('suppliers_is_active_index');
            });
        }

        if (Schema::hasTable('supplies')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->dropIndex('supplies_user_id_index');
                $table->dropIndex('supplies_supplier_id_index');
                $table->dropIndex('supplies_name_index');
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropIndex('order_items_order_id_index');
                $table->dropIndex('order_items_product_id_index');
                $table->dropIndex('order_items_user_id_index');
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex('clients_user_id_index');
                $table->dropIndex('clients_email_index');
                $table->dropIndex('clients_phone_index');
                $table->dropIndex('clients_is_active_index');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_user_id_index');
                $table->dropIndex('products_company_id_index');
                $table->dropIndex('products_branch_id_index');
                $table->dropIndex('products_sku_index');
                $table->dropIndex('products_is_active_index');
                $table->dropIndex('products_name_index');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_user_id_index');
                $table->dropIndex('orders_client_id_index');
                $table->dropIndex('orders_branch_id_index');
                $table->dropIndex('orders_company_id_index');
                $table->dropIndex('orders_status_index');
                $table->dropIndex('orders_user_created_index');
                $table->dropIndex('orders_status_created_index');
                $table->dropIndex('orders_scheduled_index');
            });
        }
    }

    /**
     * Check if an index exists on a table.
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

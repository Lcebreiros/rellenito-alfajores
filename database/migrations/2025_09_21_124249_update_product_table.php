<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Agregar user_id si no existe (propietario del producto)
            if (!Schema::hasColumn('products', 'user_id')) {
                $table->foreignId('user_id')
                      ->after('id')
                      ->constrained('users')
                      ->cascadeOnDelete()
                      ->comment('Usuario/sucursal propietaria del producto');
                
                $table->index('user_id', 'idx_products_user');
            }

            // Agregar company_id si no existe (empresa raíz)
            if (!Schema::hasColumn('products', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('users')
                      ->cascadeOnDelete()
                      ->comment('Empresa raíz propietaria');
                
                $table->index('company_id', 'idx_products_company');
            }

            // Agregar is_active si no existe
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')
                      ->default(true)
                      ->after('stock');
                
                $table->index('is_active', 'idx_products_active');
            }

            // Agregar soft deletes si no existe
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }

            // Otros campos que podrían faltar según tu modelo actual
            if (!Schema::hasColumn('products', 'is_shared')) {
                $table->boolean('is_shared')
                      ->default(false)
                      ->after('is_active')
                      ->comment('Producto compartido entre sucursales');
            }

            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->decimal('min_stock', 10, 2)
                      ->default(0)
                      ->after('stock')
                      ->comment('Stock mínimo');
            }

            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category', 100)->nullable()->after('description');
                $table->index('category', 'idx_products_category');
            }

            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 20)->default('unidad')->after('category');
            }

            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('price');
            }
        });

        // Poblar user_id para productos existentes (asignar al primer usuario)
        $firstUser = DB::table('users')->first();
        if ($firstUser && Schema::hasColumn('products', 'user_id')) {
            DB::table('products')
              ->whereNull('user_id')
              ->update([
                  'user_id' => $firstUser->id,
                  'company_id' => $firstUser->id, // Asumir que el primer user es empresa
              ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columnsToRemove = [
                'cost_price',
                'unit', 
                'category',
                'description',
                'min_stock',
                'is_shared',
                'deleted_at',
                'is_active',
                'company_id',
                'user_id'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('products', $column)) {
                    // Remover índices primero
                    if ($column === 'user_id') {
                        $this->dropForeignIfExists($table, ['user_id']);
                        $this->dropIndexIfExists($table, 'idx_products_user');
                    } elseif ($column === 'company_id') {
                        $this->dropForeignIfExists($table, ['company_id']);
                        $this->dropIndexIfExists($table, 'idx_products_company');
                    } elseif ($column === 'category') {
                        $this->dropIndexIfExists($table, 'idx_products_category');
                    } elseif ($column === 'is_active') {
                        $this->dropIndexIfExists($table, 'idx_products_active');
                    }
                    
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropIndexIfExists(Blueprint $table, string $index): void
    {
        try {
            $table->dropIndex($index);
        } catch (\Throwable $e) {
            // Silenciar si no existe
        }
    }

    private function dropForeignIfExists(Blueprint $table, array $columns): void
    {
        try {
            $table->dropForeign($columns);
        } catch (\Throwable $e) {
            // Silenciar si no existe
        }
    }
};
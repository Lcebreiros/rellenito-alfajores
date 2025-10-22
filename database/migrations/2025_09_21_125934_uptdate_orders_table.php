<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Agregar soft deletes si no existe
            if (!Schema::hasColumn('orders', 'deleted_at')) {
                $table->softDeletes();
            }

            // Agregar otros campos que faltan para el modelo jerárquico
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->after('client_id')
                      ->constrained('users')
                      ->cascadeOnDelete();
                
                $table->index('branch_id');
            }

            if (!Schema::hasColumn('orders', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('branch_id')
                      ->constrained('users')
                      ->cascadeOnDelete();
                
                $table->index('company_id');
            }

            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number', 50)->nullable()->after('status');
                $table->unique('order_number');
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash')->after('total');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 20)->default('pending')->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('notes');
                $table->index('sold_at');
            }

            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('sold_at');
            }

            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('discount');
            }
        });

        // Poblar datos faltantes para órdenes existentes
        DB::statement("
            UPDATE orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            SET 
                o.branch_id = COALESCE(
                    CASE 
                        WHEN u.hierarchy_level <= 1 THEN u.id
                        ELSE u.parent_id
                    END,
                    u.id
                ),
                o.company_id = COALESCE(
                    (SELECT company.id FROM users company 
                     WHERE company.hierarchy_level = 0 
                     AND (company.id = u.id OR company.id = u.parent_id 
                          OR company.id = (SELECT p.parent_id FROM users p WHERE p.id = u.parent_id))
                     LIMIT 1),
                    u.id
                ),
                o.sold_at = CASE 
                    WHEN o.status = 'completed' AND o.sold_at IS NULL THEN o.updated_at 
                    ELSE o.sold_at 
                END
            WHERE o.branch_id IS NULL OR o.company_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columnsToRemove = [
                'tax_amount',
                'discount', 
                'sold_at',
                'notes',
                'payment_status',
                'payment_method',
                'order_number',
                'company_id',
                'branch_id',
                'deleted_at'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    if ($column === 'branch_id' || $column === 'company_id') {
                        try {
                            $table->dropForeign([$column]);
                        } catch (\Throwable $e) {
                            // Ignore if doesn't exist
                        }
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
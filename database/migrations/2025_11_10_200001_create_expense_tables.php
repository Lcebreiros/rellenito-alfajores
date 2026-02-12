<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // Solo crear las tablas si NO existen (por si ya están en producción)
        if (!Schema::hasTable('supplier_expenses')) {
            Schema::create('supplier_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->string('supplier_name');
                $table->text('description')->nullable();
                $table->decimal('cost', 15, 2);
                $table->decimal('quantity', 10, 3)->default(1);
                $table->string('unit')->default('unidad');
                $table->enum('frequency', ['unica', 'diaria', 'semanal', 'mensual', 'anual'])->default('mensual');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('service_expenses')) {
            Schema::create('service_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
                $table->string('expense_name');
                $table->text('description')->nullable();
                $table->decimal('cost', 15, 2);
                $table->enum('expense_type', ['material', 'mano_obra', 'herramienta', 'otro'])->default('otro');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('third_party_services')) {
            Schema::create('third_party_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('service_name');
                $table->string('provider_name')->nullable();
                $table->text('description')->nullable();
                $table->decimal('cost', 15, 2);
                $table->enum('frequency', ['unica', 'diaria', 'semanal', 'mensual', 'anual'])->default('mensual');
                $table->date('next_payment_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('production_expenses')) {
            Schema::create('production_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->string('expense_name');
                $table->text('description')->nullable();
                $table->decimal('cost_per_unit', 15, 2);
                $table->decimal('quantity', 10, 3)->default(1);
                $table->string('unit')->default('unidad');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('service_supplies')) {
            Schema::create('service_supplies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
                $table->decimal('qty', 14, 3);
                $table->string('unit', 10);
                $table->decimal('waste_pct', 5, 2)->default(0);
                $table->timestamps();
                $table->unique(['service_id', 'supply_id']);
            });
        }

        // Agregar columnas a supplies si la tabla existe y la columna no
        if (Schema::hasTable('supplies') && !Schema::hasColumn('supplies', 'description')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->text('description')->after('name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_supplies');
        Schema::dropIfExists('production_expenses');
        Schema::dropIfExists('third_party_services');
        Schema::dropIfExists('service_expenses');
        Schema::dropIfExists('supplier_expenses');

        if (Schema::hasColumn('supplies', 'description')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};

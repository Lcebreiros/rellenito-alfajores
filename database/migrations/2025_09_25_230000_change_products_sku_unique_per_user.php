<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quitar unique global de sku si existe
            try {
                $table->dropUnique(['sku']);
            } catch (\Throwable $e) {
                // Algunos motores requieren el nombre exacto del índice
                try { $table->dropUnique('products_sku_unique'); } catch (\Throwable $e2) {}
            }

            // Unicidad por usuario
            $table->unique(['user_id', 'sku'], 'products_user_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Quitar índice compuesto
            try { $table->dropUnique('products_user_sku_unique'); } catch (\Throwable $e) {}

            // Volver a unicidad global (no recomendado, pero para rollback)
            $table->unique('sku');
        });
    }
};


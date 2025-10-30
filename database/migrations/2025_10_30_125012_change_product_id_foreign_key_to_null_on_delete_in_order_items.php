<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cambia la FK de product_id de restrictOnDelete a nullOnDelete
     * para permitir eliminar productos que fueron vendidos.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // 1. Eliminar la FK existente
            $table->dropForeign(['product_id']);

            // 2. Hacer la columna nullable
            $table->foreignId('product_id')->nullable()->change();

            // 3. Recrear la FK con nullOnDelete
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Revertir a restrictOnDelete
            $table->dropForeign(['product_id']);

            $table->foreignId('product_id')->nullable(false)->change();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->restrictOnDelete();
        });
    }
};

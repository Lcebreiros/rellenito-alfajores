<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Agregar user_id (nullable para backfill)
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();

            // FK simple para product_id
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {}

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();

            // Índices útiles
            $table->index(['user_id','product_id'], 'idx_adj_user_product');
            $table->index(['user_id','reference_type','reference_id'], 'idx_adj_user_morph');
        });
    }

    public function down(): void {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropIndex('idx_adj_user_product');
            $table->dropIndex('idx_adj_user_morph');
            $table->dropColumn('user_id');
        });
    }
};

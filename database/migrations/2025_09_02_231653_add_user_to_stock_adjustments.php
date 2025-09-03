<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Agregamos user_id (nullable para backfill; luego podés volverlo NOT NULL)
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

            // Si existía FK simple a product_id, la removemos antes de la compuesta
            try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}

            // Asegurate de tener en products el índice único (user_id,id):
            // (lo pusimos en migraciones anteriores)
            // Schema::table('products', function (Blueprint $t) {
            //   $t->unique(['user_id','id'], 'uniq_products_user_id_pk');
            // });

            // FK compuesta: (user_id, product_id) -> products(user_id, id)
            $table->foreign(['user_id','product_id'], 'fk_adj_product_user')
                  ->references(['user_id','id'])->on('products')
                  ->cascadeOnDelete();

            // Índices útiles
            $table->index(['user_id','product_id'], 'idx_adj_user_product');
            $table->index(['user_id','reference_type','reference_id'], 'idx_adj_user_morph');
        });
    }

    public function down(): void {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign('fk_adj_product_user');
            $table->dropIndex('idx_adj_user_product');
            $table->dropIndex('idx_adj_user_morph');
            $table->dropConstrainedForeignId('user_id');
            // (Opcional) Restaurar FK simple a product_id si la usabas antes
        });
    }
};

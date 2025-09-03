<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('costings', function (Blueprint $table) {
            // user_id (nullable para backfill; luego podés volverlo NOT NULL)
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Si existe FK simple de product_id, la quitamos antes de la compuesta
            try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}

            // Asegúrate de haber creado antes en products el índice único:
            // $table->unique(['user_id','id'], 'uniq_products_user_id_pk');
            // (lo hicimos en la migración previa de products)

            // FK compuesta: (user_id, product_id) -> products(user_id, id)
            $table->foreign(['user_id','product_id'], 'fk_costings_product_user')
                  ->references(['user_id','id'])->on('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropForeign('fk_costings_product_user');
            $table->dropConstrainedForeignId('user_id');
            // (si querés, podés restaurar la FK simple de product_id)
        });
    }
};

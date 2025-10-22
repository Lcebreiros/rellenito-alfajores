<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('costings', function (Blueprint $table) {
            // Agregar user_id (nullable para backfill)
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();

            // FK simple sobre product_id
            // Primero, si existía alguna FK previa, la eliminamos
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {}

            // Crear la FK simple
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('costings', function (Blueprint $table) {
            // Eliminar las FK
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            // Opcional: eliminar columna user_id
            $table->dropColumn('user_id');
        });
    }
};

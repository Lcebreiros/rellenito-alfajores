<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('order_items', function (Blueprint $table) {
            // agregar user_id si no existe
            if (!Schema::hasColumn('order_items', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained()
                      ->cascadeOnDelete();
            }

            // FK simple para order_id
            try { $table->dropForeign(['order_id']); } catch (\Throwable $e) {}

            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn('user_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            // Hacer parking_shift_id opcional (era requerido para parking)
            $table->foreignId('parking_shift_id')->nullable()->change();

            // Sesión de caja general
            $table->foreignId('cash_session_id')
                ->nullable()
                ->after('parking_shift_id')
                ->constrained('cash_sessions')
                ->nullOnDelete();

            // Venta que generó el movimiento (automático)
            $table->foreignId('order_id')
                ->nullable()
                ->after('cash_session_id')
                ->constrained('orders')
                ->nullOnDelete();
        });

        // Ampliar el enum para incluir 'sale' y 'apertura'
        DB::statement("ALTER TABLE cash_movements MODIFY COLUMN type ENUM('ingreso','egreso','sale','apertura')");

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->index('cash_session_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['cash_session_id', 'order_id']);
            $table->foreignId('parking_shift_id')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE cash_movements MODIFY COLUMN type ENUM('ingreso','egreso')");
    }
};

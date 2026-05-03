<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $isSQLite = DB::getDriverName() === 'sqlite';

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreignId('cash_session_id')
                ->nullable()
                ->after('parking_shift_id')
                ->constrained('cash_sessions')
                ->nullOnDelete();

            $table->foreignId('order_id')
                ->nullable()
                ->after('cash_session_id')
                ->constrained('orders')
                ->nullOnDelete();
        });

        // ENUM solo en MySQL; en SQLite el tipo es TEXT y no necesita modificación
        if (! $isSQLite) {
            // parking_shift_id ya es nullable desde el migration de creación
            // Solo ampliar el ENUM de tipo
            DB::statement("ALTER TABLE cash_movements MODIFY COLUMN type ENUM('ingreso','egreso','sale','apertura')");
        }

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->index('cash_session_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        $isSQLite = DB::getDriverName() === 'sqlite';

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['cash_session_id', 'order_id']);
        });

        if (! $isSQLite) {
            DB::statement("ALTER TABLE cash_movements MODIFY COLUMN type ENUM('ingreso','egreso')");
        }
    }
};

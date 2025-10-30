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
        Schema::table('users', function (Blueprint $table) {
            // Configuraciones de notificaciones de stock
            $table->boolean('notify_low_stock')->default(true)->after('timezone');
            $table->integer('low_stock_threshold')->default(5)->after('notify_low_stock');
            $table->boolean('notify_out_of_stock')->default(true)->after('low_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_low_stock', 'low_stock_threshold', 'notify_out_of_stock']);
        });
    }
};

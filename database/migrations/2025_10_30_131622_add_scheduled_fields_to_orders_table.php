<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega campos para pedidos agendados:
     * - scheduled_for: fecha/hora para la que se agenda el pedido
     * - is_scheduled: flag para identificar pedidos agendados
     * - reminder_sent_at: timestamp de cuándo se envió el recordatorio
     * - notes: notas adicionales del pedido agendado
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('orders', 'is_scheduled')) {
                $table->boolean('is_scheduled')->default(false)->after('status');
            }
            if (!Schema::hasColumn('orders', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('scheduled_for');
            }
            // notes ya existe, no la agregamos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['scheduled_for', 'is_scheduled', 'reminder_sent_at']);
        });
    }
};

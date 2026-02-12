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
        if (!Schema::hasTable('parking_shifts')) {
            return;
        }

        Schema::table('parking_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_shifts', 'initial_cash')) {
                $table->decimal('initial_cash', 12, 2)->default(0)
                    ->comment('Efectivo con el que se abre la caja');
            }
            if (!Schema::hasColumn('parking_shifts', 'expected_cash')) {
                $table->decimal('expected_cash', 12, 2)->default(0)
                    ->comment('Efectivo esperado calculado automáticamente');
            }
            if (!Schema::hasColumn('parking_shifts', 'cash_difference')) {
                $table->decimal('cash_difference', 12, 2)->default(0)
                    ->comment('Diferencia entre efectivo contado y esperado');
            }
            if (!Schema::hasColumn('parking_shifts', 'remaining_cash')) {
                $table->decimal('remaining_cash', 12, 2)->default(0)
                    ->comment('Efectivo que queda en caja para el próximo turno');
            }
            if (!Schema::hasColumn('parking_shifts', 'previous_shift_id')) {
                $table->foreignId('previous_shift_id')->nullable()
                    ->constrained('parking_shifts')->nullOnDelete()
                    ->comment('Turno anterior que dejó efectivo en caja');
            }
            if (!Schema::hasColumn('parking_shifts', 'total_discounts')) {
                $table->decimal('total_discounts', 12, 2)->default(0)
                    ->comment('Total de descuentos/bonificaciones aplicadas');
            }
            if (!Schema::hasColumn('parking_shifts', 'total_movements')) {
                $table->integer('total_movements')->default(0)
                    ->comment('Cantidad total de movimientos (ingresos+egresos completos)');
            }
            if (!Schema::hasColumn('parking_shifts', 'status')) {
                $table->enum('status', ['open', 'closed'])->default('open')
                    ->index()
                    ->comment('Estado del turno');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('parking_shifts')) {
            return;
        }

        Schema::table('parking_shifts', function (Blueprint $table) {
            $table->dropColumn([
                'initial_cash',
                'expected_cash',
                'cash_difference',
                'remaining_cash',
                'total_discounts',
                'total_movements',
                'status',
            ]);

            $table->dropConstrainedForeignId('previous_shift_id');
        });
    }
};

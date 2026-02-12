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
            // Control de caja
            $table->decimal('initial_cash', 12, 2)->default(0)->after('employee_id')
                ->comment('Efectivo con el que se abre la caja');

            $table->decimal('expected_cash', 12, 2)->default(0)->after('cash_counted')
                ->comment('Efectivo esperado calculado automáticamente');

            $table->decimal('cash_difference', 12, 2)->default(0)->after('expected_cash')
                ->comment('Diferencia entre efectivo contado y esperado (positivo=sobrante, negativo=faltante)');

            $table->decimal('remaining_cash', 12, 2)->default(0)->after('envelope_amount')
                ->comment('Efectivo que queda en caja para el próximo turno');

            // Referencia al turno anterior
            $table->foreignId('previous_shift_id')->nullable()->after('employee_id')
                ->constrained('parking_shifts')->nullOnDelete()
                ->comment('Turno anterior que dejó efectivo en caja');

            // Totales y estadísticas
            $table->decimal('total_discounts', 12, 2)->default(0)->after('mp_amount')
                ->comment('Total de descuentos/bonificaciones aplicadas');

            $table->integer('total_movements')->default(0)->after('total_discounts')
                ->comment('Cantidad total de movimientos (ingresos+egresos completos)');

            // Estado del turno
            $table->enum('status', ['open', 'closed'])->default('open')->after('started_at')
                ->index()
                ->comment('Estado del turno');
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

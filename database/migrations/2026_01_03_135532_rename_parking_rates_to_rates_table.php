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
        if (!Schema::hasTable('parking_rates')) {
            return;
        }

        // Renombrar tabla parking_rates a rates
        Schema::rename('parking_rates', 'rates');

        // Agregar campo rental_type para diferenciar tipos de alquiler
        Schema::table('rates', function (Blueprint $table) {
            $table->string('rental_type')->default('parking')->after('company_id')
                ->comment('Tipo: parking, cancha, salon, equipo, etc.');
        });

        // Actualizar foreign key en parking_stays
        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropForeign(['rate_id']);
        });

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->foreign('rate_id')->references('id')->on('rates')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir foreign key
        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropForeign(['rate_id']);
        });

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->foreign('rate_id')->references('id')->on('parking_rates')->nullOnDelete();
        });

        // Eliminar campo rental_type
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn('rental_type');
        });

        // Renombrar de vuelta
        Schema::rename('rates', 'parking_rates');
    }
};

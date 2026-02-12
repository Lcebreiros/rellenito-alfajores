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
        if (!Schema::hasTable('parking_stays') || !Schema::hasTable('parking_shifts')) {
            return;
        }

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->foreignId('parking_shift_id')->nullable()->after('company_id')
                ->constrained('parking_shifts')->nullOnDelete()
                ->comment('Turno en el que se registró este movimiento');

            $table->index('parking_shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('parking_stays') || !Schema::hasTable('parking_shifts')) {
            return;
        }

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parking_shift_id');
        });
    }
};

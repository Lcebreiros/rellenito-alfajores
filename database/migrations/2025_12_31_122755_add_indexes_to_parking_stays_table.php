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
        if (!Schema::hasTable('parking_stays')) {
            return;
        }

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_parking_stays_company_status');
            $table->index(['company_id', 'status', 'license_plate'], 'idx_parking_stays_company_status_plate');
            $table->index(['company_id', 'exit_at'], 'idx_parking_stays_company_exit');
        });

        if (Schema::hasColumn('parking_stays', 'parking_space_id')) {
            Schema::table('parking_stays', function (Blueprint $table) {
                $table->index(['parking_space_id', 'status'], 'idx_parking_stays_space_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropIndex('idx_parking_stays_company_status');
            $table->dropIndex('idx_parking_stays_company_status_plate');
            $table->dropIndex('idx_parking_stays_space_status');
            $table->dropIndex('idx_parking_stays_company_exit');
        });
    }
};

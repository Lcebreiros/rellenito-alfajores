<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_shifts') || !Schema::hasTable('employees')) {
            return;
        }

        Schema::table('parking_shifts', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('company_id')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parking_shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};

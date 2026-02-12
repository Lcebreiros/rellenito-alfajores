<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_stays')) {
            return;
        }

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('parking_space_id')->constrained('discounts')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn('discount_amount');
        });
    }
};

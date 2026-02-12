<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_stays') || !Schema::hasTable('discounts')) {
            return;
        }

        Schema::table('parking_stays', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_stays', 'discount_id')) {
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('parking_stays', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0);
            }
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

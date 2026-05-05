<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_purchases', function (Blueprint $table) {
            $table->date('purchased_at')->nullable()->after('total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('supply_purchases', function (Blueprint $table) {
            $table->dropColumn('purchased_at');
        });
    }
};

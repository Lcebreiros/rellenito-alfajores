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

        Schema::table('parking_rates', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('parking_space_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('parking_shifts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_rates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('parking_space_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('parking_shifts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_spaces')) {
            return;
        }

        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->enum('usage', ['horaria', 'mensual'])
                ->default('horaria')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('parking_spaces', function (Blueprint $table) {
            $table->dropColumn('usage');
        });
    }
};

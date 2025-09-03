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
        Schema::table('dashboard_widgets', function (Blueprint $table) {
    if (!Schema::hasColumn('dashboard_widgets', 'x')) $table->integer('x')->default(0);
    if (!Schema::hasColumn('dashboard_widgets', 'y')) $table->integer('y')->default(0);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            //
        });
    }
};

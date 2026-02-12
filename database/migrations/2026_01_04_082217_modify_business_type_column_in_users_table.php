<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // Primero, actualizar valores existentes
        DB::table('users')->whereNull('business_type')->update(['business_type' => 'comercio']);
        DB::table('users')->where('business_type', 'generic')->update(['business_type' => 'comercio']);
        DB::table('users')->where('business_type', 'tienda')->update(['business_type' => 'comercio']);
        DB::table('users')->where('business_type', 'estacionamiento')->update(['business_type' => 'alquiler']);
        DB::table('users')->where('business_type', 'empresa')->update(['business_type' => 'comercio']);

        // Modificar la columna a ENUM
        Schema::table('users', function (Blueprint $table) {
            $table->enum('business_type', ['comercio', 'alquiler'])->default('comercio')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_type')->nullable()->change();
        });
    }
};

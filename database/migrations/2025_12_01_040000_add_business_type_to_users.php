<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // comercio: tiendas, negocios de venta de productos/servicios
            // alquiler: parking, alquiler de espacios, cocheras
            $table->enum('business_type', ['comercio', 'alquiler'])->default('comercio')->after('subscription_level');
        });

        // Asignar valor por defecto para usuarios existentes
        DB::table('users')->update(['business_type' => 'comercio']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('business_type');
        });
    }
};

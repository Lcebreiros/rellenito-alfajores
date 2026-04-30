<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        if (!Schema::hasColumn('users', 'modulos_activos')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('modulos_activos')->nullable();
            });
        }

        // Actualizar usuarios existentes con todos los módulos activos por defecto
        $todosLosModulos = ['productos', 'servicios', 'proyectos', 'sucursales', 'empleados', 'clientes'];

        DB::table('users')->update([
            'modulos_activos' => json_encode($todosLosModulos)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('modulos_activos');
        });
    }
};

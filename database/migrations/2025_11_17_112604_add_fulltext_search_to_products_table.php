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
        // Agregar índice FULLTEXT en el campo name de products
        // Usar SQL directo porque Laravel no soporta FULLTEXT nativamente
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_name_fulltext (name)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índice FULLTEXT
        DB::statement('ALTER TABLE products DROP INDEX products_name_fulltext');
    }
};

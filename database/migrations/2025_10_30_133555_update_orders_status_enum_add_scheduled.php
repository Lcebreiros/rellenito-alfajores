<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Actualiza el ENUM de status para incluir 'pending' y 'scheduled'
     */
    public function up(): void
    {
        // MySQL no permite modificar ENUMs directamente con ALTER COLUMN
        // Necesitamos usar ALTER TABLE ... MODIFY
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'pending', 'scheduled', 'completed', 'canceled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original (sin pending y scheduled)
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'completed', 'canceled') NOT NULL DEFAULT 'draft'");
    }
};

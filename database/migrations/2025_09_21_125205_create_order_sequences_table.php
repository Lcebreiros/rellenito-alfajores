<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('current')
                  ->default(0)
                  ->comment('Último número de secuencia usado');
            $table->timestamps();
            
            // Índice único para evitar duplicados por sucursal
            $table->unique('branch_id', 'uk_order_sequences_branch');
            
            // Índice para búsquedas rápidas
            $table->index(['branch_id', 'current'], 'idx_order_sequences_branch_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};
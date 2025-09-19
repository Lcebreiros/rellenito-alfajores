<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();

            // Producto relacionado
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Usuario que realizó el cambio
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Valores de stock
            $table->integer('old_stock')->nullable();
            $table->integer('new_stock');

            // Tipo de acción: creado, actualizado, incrementado, disminuido
            $table->string('action');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_history');
    }
};

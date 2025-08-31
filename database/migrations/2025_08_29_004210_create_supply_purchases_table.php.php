<?php

// database/migrations/2025_08_28_000002_create_supply_purchases_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('supply_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 14, 3);               // cantidad comprada (en unidad declarada abajo)
            $table->string('unit', 10);                  // 'kg','g','l','ml','u'
            $table->decimal('unit_to_base', 14, 6);      // factor -> base (ej: kg->g = 1000)
            $table->decimal('total_cost', 14, 2);        // costo total compra
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('supply_purchases'); }
};


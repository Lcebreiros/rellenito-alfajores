<?php

// database/migrations/2025_08_27_000004_create_stock_adjustments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('stock_adjustments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_id')->constrained()->cascadeOnDelete();
      $table->integer('quantity_change'); // positivo suma, negativo descuenta
      $table->string('reason')->nullable(); // "order", "manual", etc.
      // referencia polimórfica opcional (p.ej., order)
      $table->nullableMorphs('reference');
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('stock_adjustments'); }
};

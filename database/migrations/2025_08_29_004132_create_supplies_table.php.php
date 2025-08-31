<?php

// database/migrations/2025_08_28_000001_create_supplies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('supplies', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->enum('base_unit', ['g','ml','u'])->default('g'); // unidad base del insumo
      $table->decimal('stock_base_qty', 14, 3)->default(0);    // stock en unidad base
      $table->decimal('avg_cost_per_base', 14, 6)->default(0); // $ por unidad base
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('supplies'); }
};

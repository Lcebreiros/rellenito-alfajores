<?php

// database/migrations/2025_08_28_000003_create_product_recipes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('product_recipes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
      $table->decimal('qty', 14, 3);        // cantidad usada
      $table->string('unit', 10);           // g,kg,ml,l,u,cm3
      $table->decimal('waste_pct', 5, 2)->default(0); // % merma
      $table->timestamps();
      $table->unique(['product_id','supply_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('product_recipes'); }
};

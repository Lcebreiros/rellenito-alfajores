<?php

// database/migrations/2025_08_28_000000_add_yield_units_to_products.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('products', function (Blueprint $table) {
      $table->unsignedInteger('yield_units')->default(1)->after('price');
    });
  }
  public function down(): void {
    Schema::table('products', function (Blueprint $table) {
      $table->dropColumn('yield_units');
    });
  }
};

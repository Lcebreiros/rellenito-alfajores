<?php

// database/migrations/2025_01_01_000000_create_cost_analyses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cost_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()
                  ->constrained('products')->nullOnDelete();
            $table->string('source', 10); // 'simple' | 'recipe'
            $table->unsignedInteger('yield_units');
            $table->decimal('unit_total', 12, 4);
            $table->decimal('batch_total', 12, 4);
            $table->json('lines'); // [{id,name,base_unit,per_unit_qty,per_unit_cost,perc}]
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cost_analyses');
    }
};


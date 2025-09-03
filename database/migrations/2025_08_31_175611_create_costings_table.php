<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('costings', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('recipe'); // recipe | quick
            $table->unsignedInteger('yield_units')->default(1);
            $table->decimal('unit_total', 12, 2)->default(0);
            $table->decimal('batch_total', 12, 2)->default(0);

            // json con líneas de ingredientes
            $table->json('lines')->nullable();

            // relación con productos
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costings');
    }
};

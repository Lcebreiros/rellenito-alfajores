<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // ej. 'gym', 'health_insurance'
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_benefit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_id')->constrained('benefits')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->json('meta')->nullable(); // p.ej. plan, nivel
            $table->timestamps();

            $table->unique(['benefit_id','employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_benefit');
        Schema::dropIfExists('benefits');
    }
};

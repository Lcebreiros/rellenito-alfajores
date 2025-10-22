<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete(); // quien evaluó
            $table->decimal('score', 5, 2)->nullable();
            $table->string('type')->nullable(); // p.ej. anual, trimestral
            $table->text('notes')->nullable();
            $table->date('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_evaluations');
    }
};

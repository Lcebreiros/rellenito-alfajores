<?php

// database/migrations/2025_09_24_000001_create_employees_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dni')->unique(); // o documento
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->date('hire_date')->nullable(); // fecha de ingreso
            $table->string('position')->nullable(); // puesto
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('photo_path')->nullable(); // foto opcional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

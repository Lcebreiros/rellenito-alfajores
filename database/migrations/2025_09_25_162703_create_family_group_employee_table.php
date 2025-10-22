<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('family_group_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained('family_groups')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('relationship')->nullable(); // padre, hijo, conyuge, etc.
            $table->timestamps();

            $table->unique(['family_group_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_group_employee');
    }
};

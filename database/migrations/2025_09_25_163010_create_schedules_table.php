<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->tinyInteger('weekday')->comment('0=domingo,1=lunes,...6=sabado');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id','weekday','starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_duration_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_space_id')->constrained('rental_spaces')->cascadeOnDelete();
            $table->string('label'); // "1 hora", "1 hora y media", "2 horas"
            $table->unsignedSmallInteger('minutes'); // 60, 90, 120
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_space_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_duration_options');
    }
};

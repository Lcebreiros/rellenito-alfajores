<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_space_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // hex color
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rental_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('rental_space_categories')->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // hex color for calendar display
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_spaces');
        Schema::dropIfExists('rental_space_categories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->enum('type', ['percentage', 'fixed', 'free_minutes'])->default('percentage');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('partner')->nullable()->comment('Ej: restaurante con convenio');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('discountables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->morphs('discountable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discountables');
        Schema::dropIfExists('discounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->boolean('email_delivery')->default(false);
            $table->timestamp('next_generation_at')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id'); // Un config por usuario
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_configurations');
    }
};

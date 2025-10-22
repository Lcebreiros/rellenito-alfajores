<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_user_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('current')->default(0);
            $table->timestamps();

            $table->unique('user_id', 'uk_order_user_sequences_user');
            $table->index(['user_id', 'current'], 'idx_order_user_sequences_user_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_user_sequences');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_amount', 12, 2)->default(0);
            $table->decimal('closing_amount', 12, 2)->nullable();
            $table->text('closing_note')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};

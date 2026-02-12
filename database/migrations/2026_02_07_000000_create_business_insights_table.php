<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_insights')) {
            return;
        }

        Schema::create('business_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('organization_id')->nullable();
            $table->enum('type', [
                'stock_alert',
                'revenue_opportunity',
                'cost_warning',
                'trend',
                'client_retention',
                'prediction',
                'reminder'
            ]);
            $table->enum('priority', ['critical', 'high', 'medium', 'low']);
            $table->string('title');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_route')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Índices para optimizar queries
            $table->index(['user_id', 'is_dismissed', 'expires_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'priority']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_insights');
    }
};

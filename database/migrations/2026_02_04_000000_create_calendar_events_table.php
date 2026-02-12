<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendar_events')) {
            return;
        }

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('organization_id')->nullable();
            $table->enum('event_type', [
                'order_delivery',
                'payment_deadline',
                'tax_due',
                'supply_reorder',
                'expense_payment',
                'reminder',
                'custom'
            ])->default('custom');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#7534C9');
            $table->dateTime('event_date');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('reminder_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'overdue', 'cancelled'])->default('pending');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

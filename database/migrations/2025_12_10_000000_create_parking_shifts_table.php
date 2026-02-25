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

        Schema::create('parking_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('operator_name');
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->decimal('incomes_total', 12, 2)->default(0);
            $table->decimal('cash_counted', 12, 2)->default(0);
            $table->decimal('envelope_amount', 12, 2)->default(0);
            $table->decimal('mp_amount', 12, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_shifts');
    }
};

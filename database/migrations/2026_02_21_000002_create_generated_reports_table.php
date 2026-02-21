<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('frequency_type', ['weekly', 'monthly', 'quarterly', 'semiannual', 'annual', 'manual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};

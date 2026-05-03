<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_movements')) {
            return;
        }

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();

            // nullable desde el inicio — parking es opcional para nuevas instalaciones
            if (Schema::hasTable('parking_shifts')) {
                $table->foreignId('parking_shift_id')->nullable()->constrained('parking_shifts')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('parking_shift_id')->nullable();
            }

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // enum en MySQL, string en SQLite
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('created_by');
            if (Schema::hasTable('parking_shifts')) {
                $table->index(['parking_shift_id', 'type']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};

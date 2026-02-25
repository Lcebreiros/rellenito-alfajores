<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rental_space_id')->constrained('rental_spaces')->cascadeOnDelete();
            $table->foreignId('rental_duration_option_id')->nullable()->constrained('rental_duration_options')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();

            // Datos del cliente (para reservas rápidas sin cliente registrado)
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();

            // Horario
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('duration_minutes');

            // Estado y monto
            $table->enum('status', ['pending', 'confirmed', 'finished', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->string('google_calendar_event_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Índices de consulta frecuente
            $table->index(['company_id', 'status', 'starts_at'], 'bookings_company_status_starts_idx');
            $table->index(['rental_space_id', 'starts_at', 'ends_at'], 'bookings_space_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

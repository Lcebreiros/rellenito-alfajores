<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_training', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            
            // Fechas y progreso
            $table->date('assigned_date'); // Cuándo se le asignó
            $table->date('start_date')->nullable(); // Cuándo comenzó
            $table->date('completion_date')->nullable(); // Cuándo terminó
            $table->date('due_date')->nullable(); // Fecha límite (para obligatorias)
            $table->date('next_due_date')->nullable(); // Para capacitaciones recurrentes
            
            // Estado y resultados
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'failed', 'expired', 'cancelled'])
                  ->default('assigned');
            $table->decimal('score', 5, 2)->nullable(); // Puntuación obtenida (0-100)
            $table->decimal('passing_score', 5, 2)->nullable(); // Puntuación mínima requerida
            $table->boolean('passed')->default(false);
            
            // Información adicional
            $table->integer('attempts')->default(0); // Intentos realizados
            $table->integer('hours_completed')->nullable();
            $table->text('feedback')->nullable(); // Comentarios del instructor
            $table->text('employee_notes')->nullable(); // Notas del empleado
            
            // Certificación
            $table->string('certificate_path')->nullable(); // Ruta del certificado generado
            $table->date('certificate_expiry')->nullable(); // Si el certificado vence
            
            // Metadatos
            $table->json('progress_data')->nullable(); // Datos de progreso detallado
            $table->json('meta')->nullable(); // Información adicional
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices y constraints
            $table->index(['employee_id', 'status']);
            $table->index(['training_id', 'status']);
            $table->index(['employee_id', 'completion_date']);
            $table->index(['due_date', 'status']); // Para encontrar capacitaciones vencidas
            
            // Un empleado puede tener múltiples registros de la misma capacitación (recurrentes)
            $table->index(['employee_id', 'training_id', 'assigned_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training');
    }
};
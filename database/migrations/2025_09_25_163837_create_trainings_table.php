<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            
            // Relación con la empresa
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            
            // Datos básicos de la capacitación
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category'); // 'safety', 'technical', 'soft_skills', 'compliance', etc.
            $table->string('level'); // 'beginner', 'intermediate', 'advanced'
            
            // Detalles de la capacitación
            $table->string('provider')->nullable(); // Empresa/institución que la dicta
            $table->string('instructor_name')->nullable();
            $table->string('location')->nullable(); // 'online', 'office', 'external'
            $table->integer('duration_hours')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            
            // Configuración y requisitos
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_recurring')->default(false); // Si se debe repetir periódicamente
            $table->integer('recurrence_months')->nullable(); // Cada cuántos meses se repite
            $table->json('prerequisites')->nullable(); // Capacitaciones previas necesarias
            
            // Fechas disponibles
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            // Recursos y materiales
            $table->json('materials')->nullable(); // Links, archivos, recursos
            $table->json('meta')->nullable(); // Información adicional
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'is_mandatory']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
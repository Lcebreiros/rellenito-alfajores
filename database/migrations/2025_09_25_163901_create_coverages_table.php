<?php

// 1. COVERAGES_TABLE - Catálogo de coberturas médicas/seguros
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coverages', function (Blueprint $table) {
            $table->id();
            
            // Relación con la empresa/usuario company
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            
            // Datos básicos de la cobertura
            $table->string('name'); // "OSDE 210", "Swiss Medical", "Obra Social UOM"
            $table->string('type'); // 'health', 'life', 'accident', 'dental', 'vision'
            $table->string('provider'); // "OSDE", "Swiss Medical", "Galeno"
            $table->text('description')->nullable();
            
            // Costos y cobertura
            $table->decimal('monthly_cost', 10, 2)->nullable(); // Costo mensual
            $table->decimal('employee_contribution', 5, 2)->nullable(); // % que paga empleado
            $table->decimal('company_contribution', 5, 2)->nullable(); // % que paga empresa
            
            // Detalles de cobertura
            $table->json('coverage_details')->nullable(); // servicios incluidos, límites, etc.
            $table->boolean('includes_family')->default(false);
            $table->integer('max_family_members')->nullable();
            
            // Configuración
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(false); // Si es obligatoria para ciertos empleados
            
            // Metadatos flexibles
            $table->json('meta')->nullable(); // info adicional, contactos, etc.
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coverages');
    }
};
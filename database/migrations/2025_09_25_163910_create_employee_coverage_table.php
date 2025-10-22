<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_coverage', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('coverage_id')->constrained('coverages')->cascadeOnDelete();
            
            // Fechas de vigencia
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            
            // Datos específicos del empleado en esta cobertura
            $table->string('policy_number')->nullable(); // Número de póliza/afiliado
            $table->decimal('custom_contribution', 10, 2)->nullable(); // Si tiene descuento/recargo especial
            
            // Estado y configuración
            $table->enum('status', ['active', 'inactive', 'pending', 'cancelled'])->default('active');
            $table->boolean('includes_family')->default(false);
            $table->integer('covered_family_members')->default(0);
            
            // Información adicional
            $table->text('notes')->nullable();
            $table->json('beneficiaries')->nullable(); // Lista de familiares cubiertos
            $table->json('meta')->nullable(); // Datos específicos adicionales
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices y constraints
            $table->index(['employee_id', 'status']);
            $table->index(['coverage_id', 'status']);
            $table->unique(['employee_id', 'coverage_id', 'effective_date'], 'employee_coverage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_coverage');
    }
};
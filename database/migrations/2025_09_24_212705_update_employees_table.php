<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // relaciones
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // opcional, si empleado tiene user

            // datos personales
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->index();
            $table->string('dni')->nullable()->index();

            // foto (legacy) - preferir documents polimórfico
            $table->string('photo_path')->nullable();

            // contrato y laborales
            $table->enum('contract_type', ['temporal','indefinido','por_hora','contrato_clase'])->nullable();
            $table->date('start_date')->nullable();
            $table->string('role')->nullable();

            // hardware / flags
            $table->boolean('has_computer')->default(false);

            // campo flexible para pequeñas notas/metadatos
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // índices compuestos
            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

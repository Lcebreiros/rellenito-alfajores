<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('arca_configurations')) {
            return;
        }

        Schema::create('arca_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('cuit', 13)->index();
            $table->string('business_name');
            $table->enum('tax_condition', ['IVA Responsable Inscripto', 'Monotributo', 'Exento', 'No Responsable', 'Consumidor Final'])->default('IVA Responsable Inscripto');
            $table->enum('environment', ['testing', 'production'])->default('testing');

            // Certificado y clave (encriptados)
            $table->text('certificate')->nullable()->comment('Certificado .crt encriptado');
            $table->text('private_key')->nullable()->comment('Clave privada .key encriptada');
            $table->text('certificate_password')->nullable()->comment('Password del certificado encriptado');

            // Configuración de puntos de venta
            $table->json('sale_points')->nullable()->comment('Array de puntos de venta configurados');
            $table->integer('default_sale_point')->default(1);

            // Estado y metadatos
            $table->boolean('is_active')->default(false);
            $table->timestamp('certificate_expires_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('enabled_voucher_types')->nullable()->comment('Tipos de comprobantes habilitados (A, B, C, etc)');

            $table->timestamps();

            // Índices
            $table->unique(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arca_configurations');
    }
};

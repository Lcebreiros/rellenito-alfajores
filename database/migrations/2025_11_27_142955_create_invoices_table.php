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
        if (app()->environment('testing')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');

            // Datos del comprobante
            $table->enum('voucher_type', ['FC-A', 'FC-B', 'FC-C', 'NC-A', 'NC-B', 'NC-C', 'ND-A', 'ND-B', 'ND-C', 'RECIBO', 'PRESUPUESTO'])->index();
            $table->integer('sale_point')->comment('Punto de venta');
            $table->bigInteger('voucher_number')->comment('Número de comprobante');
            $table->string('full_number')->virtualAs('CONCAT(sale_point, "-", LPAD(voucher_number, 8, "0"))');

            // Datos del cliente
            $table->string('client_name');
            $table->string('client_cuit', 13)->nullable();
            $table->string('client_tax_id', 13)->nullable()->comment('DNI u otro doc');
            $table->string('client_address')->nullable();
            $table->enum('client_tax_condition', ['IVA Responsable Inscripto', 'Monotributo', 'Exento', 'No Responsable', 'Consumidor Final'])->default('Consumidor Final');

            // Importes
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Monto de IVA');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('taxed_amount', 15, 2)->default(0)->comment('Neto gravado');
            $table->decimal('untaxed_amount', 15, 2)->default(0)->comment('Neto no gravado');
            $table->decimal('exempt_amount', 15, 2)->default(0)->comment('Exento');

            // Datos ARCA
            $table->string('cae', 14)->nullable()->comment('CAE de ARCA');
            $table->date('cae_expiration')->nullable();
            $table->text('arca_response')->nullable()->comment('Respuesta JSON de ARCA');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft')->index();

            // Metadatos
            $table->date('invoice_date');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->unique(['company_id', 'voucher_type', 'sale_point', 'voucher_number'], 'unique_invoice');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

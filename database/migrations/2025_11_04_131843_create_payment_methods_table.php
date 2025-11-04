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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // ej: "Efectivo", "MercadoPago", "Transferencia"
            $table->string('slug')->nullable(); // identificador único para integraciones
            $table->string('icon')->nullable(); // icono heroicon
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_gateway')->default(false); // true si necesita integración con API
            $table->json('gateway_config')->nullable(); // configuración de pasarela (API keys, etc.)
            $table->string('gateway_provider')->nullable(); // ej: 'mercadopago', 'paypal', 'stripe'
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->unique(['user_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

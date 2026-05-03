<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mercadopago_credentials', function (Blueprint $table) {
            $table->id();

            // Empresa dueña de estas credenciales
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Datos de la cuenta MP conectada (para mostrar en UI)
            $table->string('mp_user_id')->nullable();
            $table->string('mp_email')->nullable();
            $table->string('mp_nickname')->nullable();

            // Tokens cifrados en reposo
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->default('bearer');
            $table->string('scope')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Point device seleccionado para cobros presenciales
            $table->string('selected_device_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mercadopago_credentials');
    }
};

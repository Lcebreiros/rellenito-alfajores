<?php

// database/migrations/2025_09_22_000000_create_invitations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            
            // Usuario master que creó la invitación
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Tipo de invitación y nivel de suscripción
            $table->enum('invitation_type', ['company', 'admin', 'user'])->index();
            $table->string('subscription_level')->nullable();
            
            // Sistema de permisos flexible
            $table->json('permissions')->nullable();
            
            // Triple sistema de keys para máxima seguridad
            $table->string('key_hash'); // bcrypt para verificación final
            $table->string('key_fingerprint')->unique(); // HMAC-SHA256 para búsqueda indexada
            $table->string('key_plain')->nullable(); // mostrar solo una vez, luego null
            
            // Control de tiempo y uso
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users');
            
            // Estado de la invitación
            $table->enum('status', ['pending', 'used', 'revoked', 'expired'])
                  ->default('pending')
                  ->index();
            
            // Configuración específica para companies
            $table->integer('max_users')->nullable()->comment('Solo para invitation_type = company');
            
            // Notas internas del master
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices compuestos para consultas frecuentes
            $table->index(['status', 'expires_at']);
            $table->index(['created_by', 'invitation_type']);
            $table->index(['invitation_type', 'subscription_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
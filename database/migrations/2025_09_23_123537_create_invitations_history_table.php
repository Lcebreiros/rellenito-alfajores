<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations_history', function (Blueprint $table) {
            $table->id();

            // referenciamos la invitación original (opcional pero útil)
            $table->unsignedBigInteger('invitation_id')->nullable()->index();

            // campos que suelen existir en invitations (ajustá a tu esquema real)
            $table->string('key')->nullable()->index();
            $table->string('email')->nullable();
            $table->text('notes')->nullable(); // si tenés otros campos
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by')->nullable()->index(); // user id que la usó

            // guardamos metadatos de auditoría
            $table->json('payload')->nullable(); // copia completa opcional como JSON
            $table->timestamps();

            // fk hacia users (nullable) para mantener integridad histórica
            $table->foreign('used_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations_history', function (Blueprint $table) {
            $table->dropForeign(['used_by']);
        });
        Schema::dropIfExists('invitations_history');
    }
};

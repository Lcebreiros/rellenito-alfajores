<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_layouts', function (Blueprint $table) {
            $table->id();

            // Relación con users (borra el layout si se borra el usuario)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nombre opcional (por si querés soportar varios layouts por usuario)
            $table->string('name')->nullable();

            // Marcar uno como predeterminado (si manejás múltiples)
            $table->boolean('is_default')->default(false);

            // El layout propiamente dicho
            $table->json('layout_data'); // en SQLite será TEXT; en MySQL >=5.7 es JSON

            $table->timestamps();

            // Si manejás UN layout por usuario:
            $table->unique('user_id');

            // Si querés permitir múltiples layouts por usuario, comentá la línea anterior
            // y usá esta combinación única:
            // $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_layouts');
    }
};

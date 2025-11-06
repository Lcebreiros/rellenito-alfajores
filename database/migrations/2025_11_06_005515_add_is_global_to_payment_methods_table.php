<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Indica si es un método global configurado por el master
            $table->boolean('is_global')->default(false)->after('is_active');

            // Tabla pivote para asociar métodos globales con usuarios que los activan
            Schema::create('user_payment_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'payment_method_id']);
            });
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_payment_methods');

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
};

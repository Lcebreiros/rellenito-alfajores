<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'tags')) {
                $table->json('tags')->nullable()->after('description')->comment('Etiquetas/categorías del servicio');
            }
        });

        if (Schema::hasTable('service_variants')) {
            return;
        }

        Schema::create('service_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('duration_minutes')->nullable()->comment('Duración estimada en minutos');
            $table->decimal('price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Schema::dropIfExists('service_variants');

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'tags')) {
                $table->dropColumn('tags');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_rates')) {
            return;
        }

        if (!Schema::hasTable('parking_space_categories')) {
            Schema::create('parking_space_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('parking_spaces')) {
            Schema::create('parking_spaces', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('parking_space_categories')->nullOnDelete();
                $table->foreignId('rate_id')->nullable()->constrained('parking_rates')->nullOnDelete();
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->string('name');
                $table->string('code')->nullable()->index();
                $table->enum('status', ['disponible', 'ocupada', 'alquilada', 'mantenimiento'])->default('disponible')->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('parking_stays') && !Schema::hasColumn('parking_stays', 'parking_space_id')) {
            Schema::table('parking_stays', function (Blueprint $table) {
                $table->foreignId('parking_space_id')->nullable()->constrained('parking_spaces')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('parking_stays', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parking_space_id');
        });

        Schema::dropIfExists('parking_spaces');
        Schema::dropIfExists('parking_space_categories');
    }
};

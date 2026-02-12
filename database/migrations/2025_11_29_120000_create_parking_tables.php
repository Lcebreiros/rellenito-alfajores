<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parking_rates')) {
            Schema::create('parking_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->string('vehicle_type')->nullable()->comment('auto, moto, camioneta, etc');
                $table->unsignedInteger('fraction_minutes')->default(30)->comment('Fracción base, ej 30 min');
                $table->decimal('price_per_fraction', 12, 2)->default(0);
                $table->unsignedInteger('initial_block_minutes')->nullable()->comment('Bloque inicial sin fraccionar, ej 60 (1h completa)');
                $table->decimal('initial_block_price', 12, 2)->nullable()->comment('Precio del bloque inicial');
                $table->decimal('half_day_price', 12, 2)->nullable()->comment('Hasta 12h');
                $table->decimal('day_price', 12, 2)->nullable()->comment('24h');
                $table->decimal('week_price', 12, 2)->nullable();
                $table->decimal('month_price', 12, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('parking_stays')) {
            Schema::create('parking_stays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('rate_id')->nullable()->constrained('parking_rates')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('license_plate')->index();
                $table->string('vehicle_type')->nullable();
                $table->dateTime('entry_at')->index();
                $table->dateTime('exit_at')->nullable()->index();
                $table->enum('status', ['open', 'closed'])->default('open')->index();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->json('pricing_breakdown')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_stays');
        Schema::dropIfExists('parking_rates');
    }
};

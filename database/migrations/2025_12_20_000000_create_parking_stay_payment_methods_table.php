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

        Schema::create('parking_stay_payment_method', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_stay_id')->constrained('parking_stays')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['parking_stay_id', 'payment_method_id'], 'pspm_unique_stay_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_stay_payment_method');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('supply_purchases')) return;
        Schema::create('supply_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty', 10, 3);
            $table->string('unit', 10);
            $table->decimal('unit_to_base', 10, 6)->default(1);
            $table->decimal('total_cost', 10, 2);
            $table->date('purchased_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('supply_purchases');
    }
};

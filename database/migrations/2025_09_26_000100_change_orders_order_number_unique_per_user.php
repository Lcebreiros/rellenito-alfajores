<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop global unique index on order_number if exists
            try { $table->dropUnique(['order_number']); } catch (\Throwable $e) {
                try { $table->dropUnique('orders_order_number_unique'); } catch (\Throwable $e2) {}
            }

            // Add composite uniqueness (user_id, order_number)
            $table->unique(['user_id', 'order_number'], 'uk_orders_user_order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try { $table->dropUnique('uk_orders_user_order_number'); } catch (\Throwable $e) {}
            $table->unique('order_number');
        });
    }
};


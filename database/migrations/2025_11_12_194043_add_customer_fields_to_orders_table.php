<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('client_id');
                }
                if (!Schema::hasColumn('orders', 'customer_email')) {
                    $table->string('customer_email')->nullable()->after('customer_name');
                }
                if (!Schema::hasColumn('orders', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_email');
                }
                if (!Schema::hasColumn('orders', 'shipping_address')) {
                    $table->text('shipping_address')->nullable()->after('customer_phone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $drop = [];
                foreach (['customer_name', 'customer_email', 'customer_phone', 'shipping_address'] as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $drop[] = $column;
                    }
                }
                if ($drop) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};

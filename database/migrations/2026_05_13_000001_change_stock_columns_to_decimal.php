<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // products.stock: unsignedInteger → decimal(10,3)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock', 10, 3)->default(0)->unsigned()->change();
        });

        // stock_adjustments: quantity_change and new_stock integer → decimal(10,3)
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->decimal('quantity_change', 10, 3)->change();
            $table->decimal('new_stock', 10, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->change();
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->integer('quantity_change')->change();
            $table->integer('new_stock')->default(0)->change();
        });
    }
};

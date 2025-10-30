<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode', 64)->nullable()->after('sku');
                $table->index('barcode', 'idx_products_barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'barcode')) {
                try { $table->dropIndex('idx_products_barcode'); } catch (\Throwable $e) {}
                $table->dropColumn('barcode');
            }
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('supplier_expenses')) return;
        Schema::table('supplier_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_expenses', 'category')) {
                $table->string('category', 50)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('supplier_expenses', 'expense_date')) {
                $table->date('expense_date')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->dropColumn(['category', 'expense_date']);
        });
    }
};

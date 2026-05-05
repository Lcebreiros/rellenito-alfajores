<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('is_active');
            $table->date('expense_date')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->dropColumn(['category', 'expense_date']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_purchases', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete(); // si borran user → user_id se pone NULL
        });
    }

    public function down(): void
    {
        Schema::table('supply_purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

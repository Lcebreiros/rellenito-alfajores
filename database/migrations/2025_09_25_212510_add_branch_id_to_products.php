<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // nullable porque puede haber productos "globales" de company
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');

            // index y FK opcional (descomenta FK si querés integridad referencial
            $table->index('branch_id');
            // si usás users como branches y querés FK:
            // $table->foreign('branch_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // si tenés FK descomentada, dropeala primero:
            // $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};

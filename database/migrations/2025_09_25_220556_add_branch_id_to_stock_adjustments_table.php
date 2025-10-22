<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchIdToStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');

            // Si tus sucursales son usuarios (users.id) o tenés una tabla branches:
            // $table->foreign('branch_id')->references('id')->on('users')->onDelete('set null');
            // o si usás branches:
            // $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // if foreign key added, dropForeign first:
            // $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
}

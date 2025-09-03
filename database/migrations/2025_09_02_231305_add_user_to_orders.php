<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('orders', function (Blueprint $table) {
      $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

      // si tenés un número/código de pedido, podés hacerlo único por usuario:
      // $table->unique(['user_id','number'], 'uniq_orders_user_number');
    });
  }
  public function down(): void {
    Schema::table('orders', function (Blueprint $table) {
      // $table->dropUnique('uniq_orders_user_number');
      $table->dropConstrainedForeignId('user_id');
    });
  }
};

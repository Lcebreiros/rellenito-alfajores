<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('order_items', function (Blueprint $table) {
      // agrega si no existe
      if (!Schema::hasColumn('order_items','user_id')) {
        $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
      }

      // si existen FKs simples, las quitamos para poner compuestas
      try { $table->dropForeign(['order_id']); } catch (\Throwable $e) {}

      // (user_id, order_id) -> orders(user_id, id)
      // primero garantizamos índice en orders (si no lo tenés ya)
      Schema::table('orders', function (Blueprint $t) {
        $t->unique(['user_id','id'], 'uniq_orders_user_id_pk');
      });

      $table->foreign(['user_id','order_id'], 'fk_items_order_user')
            ->references(['user_id','id'])->on('orders')
            ->cascadeOnDelete();

      // (opcional) si querés bloquear cruces con productos de otro user:
      // try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}
      // Schema::table('products', function (Blueprint $t) {
      //   $t->unique(['user_id','id'], 'uniq_products_user_id_pk');
      // });
      // $table->foreign(['user_id','product_id'], 'fk_items_product_user')
      //       ->references(['user_id','id'])->on('products')
      //       ->cascadeOnDelete();
    });
  }
  public function down(): void {
    Schema::table('order_items', function (Blueprint $table) {
      $table->dropForeign('fk_items_order_user');
      // $table->dropForeign('fk_items_product_user');
      $table->dropConstrainedForeignId('user_id');
    });
    Schema::table('orders', function (Blueprint $t) {
      $t->dropUnique('uniq_orders_user_id_pk');
    });
  }
};

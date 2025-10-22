<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending')->after('id');
            } else {
                // Si la columna existe pero querés asegurarte del default o longitud:
                // DB::statement("ALTER TABLE `orders` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'pending'");
            }

            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }

            if (Schema::hasColumn('orders', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            // NO eliminamos `status` en down por seguridad si ya existía antes.
            // Si querés eliminarla, descomenta la siguiente línea **solo si estás seguro**:
            // if (Schema::hasColumn('orders', 'status')) { $table->dropColumn('status'); }
        });
    }
};

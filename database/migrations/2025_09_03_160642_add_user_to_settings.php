<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('settings', function (Blueprint $table) {
            // 1) columna user_id (nullable para backfill)
            if (!Schema::hasColumn('settings', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained()
                      ->cascadeOnDelete();
            }

            // 2) si existía unique en key global, lo quitamos
            try { $table->dropUnique('settings_key_unique'); } catch (\Throwable $e) {}

            // 3) índices/unique por usuario
            try { $table->index(['user_id','key'], 'idx_settings_user_key'); } catch (\Throwable $e) {}
            try { $table->unique(['user_id','key'], 'uniq_settings_user_key'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void {
        Schema::table('settings', function (Blueprint $table) {
            try { $table->dropUnique('uniq_settings_user_key'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_settings_user_key'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('settings', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};

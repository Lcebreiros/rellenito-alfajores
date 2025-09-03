<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Columna user_id solo si NO existe
        if (! Schema::hasColumn('supplies', 'user_id')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained()
                      ->cascadeOnDelete();
            });
        }

        // 2) Índice compuesto (user_id, id) para futuras FKs compuestas
        if (! $this->indexExists('supplies', 'idx_supplies_user_id_pk')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->index(['user_id', 'id'], 'idx_supplies_user_id_pk');
            });
        }

        // 3) Único por usuario (opcional: si tenés columna name)
        if (Schema::hasColumn('supplies', 'name') && ! $this->indexExists('supplies', 'uniq_supplies_user_name')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->unique(['user_id', 'name'], 'uniq_supplies_user_name');
            });
        }
    }

    public function down(): void
    {
        // Borrar unique si existe
        if ($this->indexExists('supplies', 'uniq_supplies_user_name')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->dropUnique('uniq_supplies_user_name');
            });
        }

        // Borrar índice compuesto si existe
        if ($this->indexExists('supplies', 'idx_supplies_user_id_pk')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->dropIndex('idx_supplies_user_id_pk');
            });
        }

        // Quitar FK/columna si existe
        if (Schema::hasColumn('supplies', 'user_id')) {
            Schema::table('supplies', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }

    /** Chequear existencia de un índice por nombre (MySQL) */
    private function indexExists(string $table, string $indexName): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
            LIMIT 1
        ", [$db, $table, $indexName]);

        return ! empty($rows);
    }
};

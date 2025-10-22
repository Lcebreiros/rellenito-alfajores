<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $table = 'documents';
    protected string $indexName = 'documents_attachable_type_attachable_id_index';

    public function up(): void
    {
        // Si no existe la tabla: la creamos con la definición completa.
        if (! Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->id();

                // morphs crea attachable_type, attachable_id y su índice compuesto
                $table->morphs('attachable');

                $table->string('disk')->default('public');
                $table->string('path');
                $table->string('type')->nullable();
                $table->string('mime')->nullable();
                $table->bigInteger('size')->nullable();
                $table->string('original_name')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            return;
        }

        // Si la tabla existe: hacemos modificaciones seguras y idempotentes.
        // 1) Asegurarnos de que existen las columnas attachable_type y attachable_id
        if (! Schema::hasColumn($this->table, 'attachable_type') || ! Schema::hasColumn($this->table, 'attachable_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                // Añadimos manualmente las columnas si no existen.
                if (! Schema::hasColumn($this->table, 'attachable_type')) {
                    $table->string('attachable_type', 255)->after('id');
                }
                if (! Schema::hasColumn($this->table, 'attachable_id')) {
                    $table->unsignedBigInteger('attachable_id')->after('attachable_type');
                }
            });
        }

        // 2) Asegurarnos de que existe el índice compuesto; si no existe, lo creamos.
        if (! $this->indexExists($this->table, $this->indexName)) {
            // Antes de crear, eliminar cualquier índice con nombre diferente que cubra las mismas columnas
            // (evita colisiones en ciertos RDBMS). Ejecutamos con try/catch por seguridad.
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->index(['attachable_type', 'attachable_id'], $this->indexName);
                });
            } catch (\Throwable $e) {
                // Si no se pudo crear el índice por alguna razón, logueamos y continuamos.
                // No interrumpimos la migration para evitar estados inconsistentes.
                // Podés quitar este catch si preferís que falle.
                info('No se pudo crear el índice ' . $this->indexName . ' en documents: ' . $e->getMessage());
            }
        }

        // 3) Columnas de metadata: añadimos solo si no existen
        $columns = [
            'disk' => fn(Blueprint $table) => $table->string('disk')->default('public')->nullable(false),
            'path' => fn(Blueprint $table) => $table->string('path'),
            'type' => fn(Blueprint $table) => $table->string('type')->nullable(),
            'mime' => fn(Blueprint $table) => $table->string('mime')->nullable(),
            'size' => fn(Blueprint $table) => $table->bigInteger('size')->nullable(),
            'original_name' => fn(Blueprint $table) => $table->string('original_name')->nullable(),
        ];

        foreach ($columns as $col => $callback) {
            if (! Schema::hasColumn($this->table, $col)) {
                Schema::table($this->table, function (Blueprint $table) use ($callback) {
                    $callback($table);
                });
            }
        }

        // 4) uploaded_by: columna + FK (si no existe)
        if (! Schema::hasColumn($this->table, 'uploaded_by')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->after('original_name');
            });
        }

        // Agregar FK uploaded_by -> users.id si no existe
        if (! $this->foreignKeyExists($this->table, 'documents_uploaded_by_foreign')) {
            try {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Podría fallar si ya existe una FK con otro nombre; lo dejamos informado.
                info('No se pudo crear FK documents.uploaded_by -> users.id: ' . $e->getMessage());
            }
        }

        // 5) timestamps: si faltan, añadirlas
        if (! Schema::hasColumn($this->table, 'created_at') || ! Schema::hasColumn($this->table, 'updated_at')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No forzamos drop si la tabla ya existía antes; por seguridad, solo la borramos
        // si fue creada por esta migration (difícil de detectar), así que optamos por no tocarla.
        // Si querés forzar drop, descomenta:
        // Schema::dropIfExists($this->table);
    }

    /**
     * Comprueba si existe un índice con el nombre dado en la tabla (MySQL).
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $db = $connection->getDatabaseName();
                $result = DB::selectOne(
                    'SELECT COUNT(1) as cnt FROM information_schema.STATISTICS WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                    [$db, $table, $indexName]
                );
                return ($result && $result->cnt > 0);
            }

            // Para otros drivers intentamos un fallback simple (posible false negative)
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
            return array_key_exists($indexName, $indexes);
        } catch (\Throwable $e) {
            info('indexExists check failed for ' . $table . '.' . $indexName . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Comprueba si existe una FK con nombre dado (MySQL/Doctrine fallback).
     */
    protected function foreignKeyExists(string $table, string $fkName): bool
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $db = $connection->getDatabaseName();
                $result = DB::selectOne(
                    'SELECT COUNT(1) as cnt FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                    [$db, $table, $fkName]
                );
                return ($result && $result->cnt > 0);
            }

            $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys($table);
            foreach ($foreignKeys as $fk) {
                if ($fk->getName() === $fkName) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            info('foreignKeyExists check failed for ' . $table . '.' . $fkName . ': ' . $e->getMessage());
            return false;
        }
    }
};

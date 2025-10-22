<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // parent_id: referencia a la "empresa" o usuario padre
            if (!Schema::hasColumn('users', 'parent_id')) {
                $table->foreignId('parent_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('users')
                      ->cascadeOnUpdate()
                      ->nullOnDelete();
                
                $table->index('parent_id', 'idx_users_parent_id');
            }

            // Nivel jerárquico para consultas más eficientes 
            // (Spatie maneja los roles, esto es solo para jerarquía organizacional)
            if (!Schema::hasColumn('users', 'hierarchy_level')) {
                $table->tinyInteger('hierarchy_level')
                      ->default(2) // Cambiar default a 2 para permitir master (-1)
                      ->after('parent_id')
                      ->comment('-1=master, 0=empresa, 1=admin, 2=usuario - independiente de roles Spatie');
                
                $table->index('hierarchy_level', 'idx_users_hierarchy_level');
            }

            // Ruta jerárquica para consultas de árbol más eficientes (opcional)
            if (!Schema::hasColumn('users', 'hierarchy_path')) {
                $table->string('hierarchy_path', 500)
                      ->nullable()
                      ->after('hierarchy_level')
                      ->comment('Ruta tipo: /1/5/12 para consultas rápidas');
                
                $table->index('hierarchy_path', 'idx_users_hierarchy_path');
            }

            // Estado del usuario específico para jerarquías
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')
                      ->default(true)
                      ->after('hierarchy_path');
                
                $table->index('is_active', 'idx_users_active');
            }

            // Límites por nivel (útil para control de licencias)
            if (!Schema::hasColumn('users', 'user_limit')) {
                $table->integer('user_limit')
                      ->nullable()
                      ->after('is_active')
                      ->comment('Límite de usuarios que puede crear este nivel');
            }

            // Contexto organizacional para Spatie (guard contexts)
            if (!Schema::hasColumn('users', 'organization_context')) {
                $table->string('organization_context', 50)
                      ->nullable()
                      ->after('user_limit')
                      ->comment('Contexto organizacional para guards de Spatie');
                
                $table->index('organization_context', 'idx_users_org_context');
            }
        });

        // Índices compuestos para consultas comunes con Spatie
        Schema::table('users', function (Blueprint $table) {
            // Para buscar usuarios activos por parent 
            if (!$this->indexExists('users', 'idx_users_parent_active')) {
                $table->index(['parent_id', 'is_active'], 'idx_users_parent_active');
            }
            
            // Para contexto organizacional + jerarquía
            if (!$this->indexExists('users', 'idx_users_org_hierarchy')) {
                $table->index(['organization_context', 'hierarchy_level', 'is_active'], 'idx_users_org_hierarchy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices compuestos primero
            $this->dropIndexIfExists($table, 'idx_users_parent_active');
            $this->dropIndexIfExists($table, 'idx_users_org_hierarchy');
            
            // Eliminar columnas en orden inverso
            $columnsToRemove = [
                'organization_context',
                'user_limit', 
                'is_active',
                'hierarchy_path',
                'hierarchy_level',
                'parent_id'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    // Eliminar índices individuales
                    $this->dropIndexIfExists($table, "idx_users_{$column}");
                    $this->dropIndexIfExists($table, "idx_users_" . str_replace('_', '', $column));
                    
                    // Casos especiales para nombres de índices
                    if ($column === 'parent_id') {
                        $this->dropForeignIfExists($table, ['parent_id']);
                        $this->dropIndexIfExists($table, 'idx_users_parent_id');
                    } elseif ($column === 'hierarchy_level') {
                        $this->dropIndexIfExists($table, 'idx_users_hierarchy_level');
                    } elseif ($column === 'hierarchy_path') {
                        $this->dropIndexIfExists($table, 'idx_users_hierarchy_path');
                    } elseif ($column === 'is_active') {
                        $this->dropIndexIfExists($table, 'idx_users_active');
                    } elseif ($column === 'organization_context') {
                        $this->dropIndexIfExists($table, 'idx_users_org_context');
                    }
                    
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Verificar si existe un índice (método compatible con Laravel 10+)
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            // Método compatible con Laravel 10+
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return !empty($indexes);
        } catch (\Exception $e) {
            // Fallback: asumir que no existe si hay error
            return false;
        }
    }

    /**
     * Eliminar índice si existe
     */
    private function dropIndexIfExists(Blueprint $table, string $index): void
    {
        try {
            $table->dropIndex($index);
        } catch (\Throwable $e) {
            // Silenciar errores si el índice no existe
        }
    }

    /**
     * Eliminar foreign key si existe
     */
    private function dropForeignIfExists(Blueprint $table, array $columns): void
    {
        try {
            $table->dropForeign($columns);
        } catch (\Throwable $e) {
            // Silenciar errores si la FK no existe
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta migración actualiza las tablas de soporte existentes
     * para hacerlas compatibles con el nuevo sistema de chat/notificaciones
     */
    public function up(): void
    {
        // Actualizar tabla support_messages (tabla antigua)
        if (Schema::hasTable('support_messages')) {
            Schema::table('support_messages', function (Blueprint $table) {
                // Renombrar ticket_id a support_chat_id si existe
                if (Schema::hasColumn('support_messages', 'ticket_id') &&
                    !Schema::hasColumn('support_messages', 'support_chat_id')) {
                    $table->renameColumn('ticket_id', 'support_chat_id');
                }

                // Renombrar body a message si existe
                if (Schema::hasColumn('support_messages', 'body') &&
                    !Schema::hasColumn('support_messages', 'message')) {
                    $table->renameColumn('body', 'message');
                }

                // Agregar columnas faltantes si no existen
                if (!Schema::hasColumn('support_messages', 'is_read')) {
                    $table->boolean('is_read')->default(false)->after('message');
                }

                if (!Schema::hasColumn('support_messages', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('is_read');
                }
            });

            // Agregar índice si no existe
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'support_messages'
                AND index_name = 'support_messages_support_chat_id_created_at_index'
            ");

            if ($indexExists[0]->count == 0) {
                Schema::table('support_messages', function (Blueprint $table) {
                    $table->index(['support_chat_id', 'created_at']);
                });
            }
        }

        // Actualizar support_chats si falta alguna columna
        if (Schema::hasTable('support_chats')) {
            Schema::table('support_chats', function (Blueprint $table) {
                if (!Schema::hasColumn('support_chats', 'support_user_id')) {
                    $table->foreignId('support_user_id')->nullable()
                        ->after('user_id')
                        ->constrained('users')
                        ->onDelete('set null');
                }

                if (!Schema::hasColumn('support_chats', 'priority')) {
                    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                        ->default('medium')
                        ->after('status');
                }

                if (!Schema::hasColumn('support_chats', 'last_message_at')) {
                    $table->timestamp('last_message_at')->nullable()->after('priority');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos porque podría causar pérdida de datos
        // Si necesitas revertir, hazlo manualmente
    }
};

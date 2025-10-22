<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Nombre que por defecto suele tener la FK
    private string $fkName = 'invitations_used_by_foreign';
    private string $table = 'invitations';

    public function up(): void
    {
        // 1) Intentamos dropear la FK si existe (silencioso si no existe)
        try {
            // MySQL: DROP FOREIGN KEY requiere conocer el nombre de la constraint
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $this->table, $this->fkName));
        } catch (\Throwable $e) {
            // no hacemos nada si falla (posiblemente no existía con ese nombre)
        }

        // 2) Creamos la FK con ON DELETE SET NULL (nullOnDelete)
        try {
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL',
                    $this->table,
                    $this->fkName
                )
            );
        } catch (\Throwable $e) {
            // si falla, lanzamos para que quede claro
            throw $e;
        }
    }

    public function down(): void
    {
        // revertir: dropear la FK con SET NULL y crear una "sin comportamiento" (NO ACTION)
        try {
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $this->table, $this->fkName));
        } catch (\Throwable $e) {
            // noop
        }

        try {
            // recreamos la FK sin ON DELETE explícito (equivalente al comportamiento previo)
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`used_by`) REFERENCES `users`(`id`)',
                    $this->table,
                    $this->fkName
                )
            );
        } catch (\Throwable $e) {
            // noop
        }
    }
};

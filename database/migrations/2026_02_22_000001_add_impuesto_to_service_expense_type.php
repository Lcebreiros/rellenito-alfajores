<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE service_expenses MODIFY COLUMN expense_type ENUM('material','mano_obra','herramienta','otro','impuesto') NOT NULL DEFAULT 'otro'");
    }

    public function down(): void
    {
        // Reasigna impuesto → otro antes de quitar el valor del enum
        DB::statement("UPDATE service_expenses SET expense_type = 'otro' WHERE expense_type = 'impuesto'");
        DB::statement("ALTER TABLE service_expenses MODIFY COLUMN expense_type ENUM('material','mano_obra','herramienta','otro') NOT NULL DEFAULT 'otro'");
    }
};

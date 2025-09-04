<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL 5.7/8.0 y MariaDB aceptan JSON con default literal '[]'
        DB::statement("
            ALTER TABLE dashboard_layouts
            MODIFY layout_data JSON NOT NULL DEFAULT ('[]')
        ");
    }

    public function down(): void
    {
        // Volver al estado previo (ajustá según tu schema original)
        DB::statement("
            ALTER TABLE dashboard_layouts
            MODIFY layout_data JSON NOT NULL
        ");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE dashboard_layouts
            MODIFY layout_data JSON NOT NULL DEFAULT ('[]')
        ");
    }
    public function down(): void
    {
        DB::statement("
            ALTER TABLE dashboard_layouts
            MODIFY layout_data JSON NOT NULL
        ");
    }
};

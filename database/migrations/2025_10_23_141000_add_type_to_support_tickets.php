<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('support_tickets')) return;
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'type')) {
                $table->enum('type', ['problema','sugerencia','consulta'])->default('consulta')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('support_tickets')) return;
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};


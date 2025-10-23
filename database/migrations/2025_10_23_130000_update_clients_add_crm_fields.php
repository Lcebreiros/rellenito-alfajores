<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('clients')) return;
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'tags')) {
                $table->json('tags')->nullable()->after('country');
            }
            if (!Schema::hasColumn('clients', 'notes')) {
                $table->text('notes')->nullable()->after('tags');
            }
            if (!Schema::hasColumn('clients', 'balance')) {
                $table->decimal('balance', 12, 2)->default(0)->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clients')) return;
        Schema::table('clients', function (Blueprint $table) {
            foreach (['balance','notes','tags'] as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


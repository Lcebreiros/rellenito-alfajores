<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'address')) {
                $table->string('address')->nullable()->after('contract_file_path');
            }
            if (!Schema::hasColumn('employees', 'medical_coverage')) {
                $table->string('medical_coverage')->nullable()->after('address');
            }

            foreach ([
                'family_group', 'evaluations', 'objectives', 'tasks', 'schedules', 'benefits', 'notes'
            ] as $idx => $jsonCol) {
                if (!Schema::hasColumn('employees', $jsonCol)) {
                    $table->json($jsonCol)->nullable()->after('medical_coverage');
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            foreach (['family_group','evaluations','objectives','tasks','schedules','benefits','notes'] as $jsonCol) {
                if (Schema::hasColumn('employees', $jsonCol)) {
                    $table->dropColumn($jsonCol);
                }
            }
            if (Schema::hasColumn('employees', 'medical_coverage')) {
                $table->dropColumn('medical_coverage');
            }
            if (Schema::hasColumn('employees', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};


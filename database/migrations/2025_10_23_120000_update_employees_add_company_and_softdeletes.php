<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return; // safety: nothing to do if table doesn't exist
        }

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('employees', 'role')) {
                $table->string('role')->nullable()->after('email');
            }

            if (!Schema::hasColumn('employees', 'start_date')) {
                $table->date('start_date')->nullable()->after('role');
            }

            if (!Schema::hasColumn('employees', 'contract_type')) {
                $table->string('contract_type')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('employees', 'has_computer')) {
                $table->boolean('has_computer')->default(false)->after('contract_type');
            }

            if (!Schema::hasColumn('employees', 'contract_file_path')) {
                $table->string('contract_file_path')->nullable()->after('photo_path');
            }

            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            if (Schema::hasColumn('employees', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('employees', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('employees', 'contract_type')) {
                $table->dropColumn('contract_type');
            }

            if (Schema::hasColumn('employees', 'has_computer')) {
                $table->dropColumn('has_computer');
            }

            if (Schema::hasColumn('employees', 'contract_file_path')) {
                $table->dropColumn('contract_file_path');
            }

            if (Schema::hasColumn('employees', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};


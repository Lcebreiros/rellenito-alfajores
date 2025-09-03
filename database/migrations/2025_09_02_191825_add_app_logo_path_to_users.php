<?php

// database/migrations/2025_09_02_000001_add_app_logo_path_to_users.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('app_logo_path')->nullable()->after('has_seen_welcome');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_logo_path');
        });
    }
};

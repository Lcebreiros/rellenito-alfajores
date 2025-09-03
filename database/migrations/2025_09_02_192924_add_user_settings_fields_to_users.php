<?php

// database/migrations/2025_09_02_000010_add_user_settings_fields_to_users.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme')->default('light')->after('app_logo_path');
            $table->string('site_title')->nullable()->after('theme');
            $table->string('receipt_logo_path')->nullable()->after('site_title');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme', 'site_title', 'receipt_logo_path']);
        });
    }
};

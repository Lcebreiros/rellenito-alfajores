<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Jetstream / Teams
            $table->string('profile_photo_path', 2048)->nullable();
            $table->foreignId('current_team_id')->nullable();

            // Jerarquía y multitenancy
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('hierarchy_level')->default(0);
            $table->string('hierarchy_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('branch_limit')->nullable();
            $table->integer('user_limit')->nullable();
            $table->string('subscription_level')->nullable();
            $table->string('business_type')->nullable();
            $table->string('organization_context')->nullable();
            $table->unsignedBigInteger('representable_id')->nullable();
            $table->string('representable_type')->nullable();

            // Configuración UI / branding
            $table->boolean('has_seen_welcome')->default(false);
            $table->string('app_logo_path')->nullable();
            $table->string('receipt_logo_path')->nullable();
            $table->string('theme')->nullable();
            $table->string('site_title')->nullable();

            // Notificaciones de stock
            $table->boolean('notify_low_stock')->default(false);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('notify_out_of_stock')->default(false);
            $table->boolean('notify_by_email')->default(false);

            // Módulos activos (json)
            $table->json('modulos_activos')->nullable();

            // Two factor
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

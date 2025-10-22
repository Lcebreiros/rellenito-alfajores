<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ------------------------
        // Tabla branches
        // ------------------------
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('logo_path')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            Schema::table('branches', function (Blueprint $table) {
                if (!Schema::hasColumn('branches', 'slug')) {
                    $table->string('slug')->unique()->after('name');
                }
                if (!Schema::hasColumn('branches', 'contact_email')) {
                    $table->string('contact_email')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('branches', 'logo_path')) {
                    $table->string('logo_path')->nullable()->after('contact_email');
                }
            });
        }

        // ------------------------
        // Tabla users
        // ------------------------
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken()->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Jerarquía y sucursales
                $table->integer('hierarchy_level')->default(2);
                $table->string('hierarchy_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('branch_limit')->nullable();
                $table->integer('user_limit')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->unsignedBigInteger('representable_id')->nullable();
                $table->string('representable_type')->nullable();

                // Logos y otros
                $table->string('app_logo_path')->nullable();
                $table->string('receipt_logo_path')->nullable();
                $table->boolean('has_seen_welcome')->default(false);
                $table->string('theme')->nullable();
                $table->string('site_title')->nullable();
                $table->string('organization_context')->nullable();

                $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'branch_limit')) {
                    $table->integer('branch_limit')->nullable()->after('hierarchy_level');
                }
                if (!Schema::hasColumn('users', 'user_limit')) {
                    $table->integer('user_limit')->nullable()->after('branch_limit');
                }
                if (!Schema::hasColumn('users', 'representable_id')) {
                    $table->unsignedBigInteger('representable_id')->nullable()->after('parent_id');
                }
                if (!Schema::hasColumn('users', 'representable_type')) {
                    $table->string('representable_type')->nullable()->after('representable_id');
                }
                if (!Schema::hasColumn('users', 'hierarchy_path')) {
                    $table->string('hierarchy_path')->nullable()->after('hierarchy_level');
                }
            });
        }
    }

    public function down(): void
    {
        // Para seguridad no borramos tablas si existen (se podrían comentar)
        /*
        Schema::dropIfExists('branches');
        Schema::dropIfExists('users');
        */
    }
};

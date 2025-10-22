<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    public function up(): void
    {
        // Solo crear si no existe
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                // company_id debe coincidir exactamente con el tipo de users.id (normalmente unsigned BIGINT)
                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');

                $table->string('name');
                $table->string('slug')->unique();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('logo_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
}

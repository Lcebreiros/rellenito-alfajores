<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_work_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_group_id')->constrained('work_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // podemos usar user o employee
            $table->string('role')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['work_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_work_group');
    }
};

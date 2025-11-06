<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Asignar todos los clientes existentes al primer usuario master o company
        $firstUser = \App\Models\User::where('hierarchy_level', \App\Models\User::HIERARCHY_MASTER)
            ->orWhere('hierarchy_level', \App\Models\User::HIERARCHY_COMPANY)
            ->orderBy('id')
            ->first();

        if ($firstUser) {
            \DB::table('clients')->whereNull('user_id')->update(['user_id' => $firstUser->id]);
        }

        // Hacer user_id NOT NULL después de asignar
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

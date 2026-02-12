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
        if (app()->environment('testing')) {
            return;
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'google_calendar_event_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('google_calendar_event_id')->nullable()->after('reminder_sent_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'google_calendar_event_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('google_calendar_event_id');
            });
        }
    }
};

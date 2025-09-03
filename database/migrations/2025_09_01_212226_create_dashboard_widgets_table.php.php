// database/migrations/create_dashboard_widgets_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type'); // 'sales', 'analytics', 'tasks', etc.
            $table->integer('position')->default(0);
            $table->integer('width')->default(6); // 1-12 (Bootstrap grid)
            $table->integer('height')->default(4); // altura en unidades
            $table->boolean('is_visible')->default(true);
            $table->json('settings')->nullable(); // configuraciones específicas del widget
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
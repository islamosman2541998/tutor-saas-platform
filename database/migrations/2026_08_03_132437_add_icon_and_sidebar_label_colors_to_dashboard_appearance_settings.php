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
        Schema::table('dashboard_appearance_settings', function (Blueprint $table) {
            $table->string('dark_mode_icon_color')->default('#64748b')->after('text_color');
            $table->string('sidebar_label_color')->default('#94a3b8')->after('dark_mode_icon_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_appearance_settings', function (Blueprint $table) {
            $table->dropColumn(['dark_mode_icon_color', 'sidebar_label_color']);
        });
    }
};

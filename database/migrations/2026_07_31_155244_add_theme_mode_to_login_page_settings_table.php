<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_page_settings', function (Blueprint $table) {
            $table->enum('theme_mode', ['light', 'dark', 'user_choice'])->default('light')->after('shadow_style');
        });
    }

    public function down(): void
    {
        Schema::table('login_page_settings', function (Blueprint $table) {
            $table->dropColumn('theme_mode');
        });
    }
};

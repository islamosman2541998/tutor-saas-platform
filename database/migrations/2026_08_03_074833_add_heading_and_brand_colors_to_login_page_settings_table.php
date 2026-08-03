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
        Schema::table('login_page_settings', function (Blueprint $table) {
            $table->string('brand_name_color')->default('#0f172a')->after('title_color');
            $table->string('heading_color')->default('#0f172a')->after('brand_name_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_page_settings', function (Blueprint $table) {
            $table->dropColumn(['brand_name_color', 'heading_color']);
        });
    }
};

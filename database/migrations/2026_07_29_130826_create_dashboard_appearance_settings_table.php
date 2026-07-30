<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_appearance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('primary_color')->default('#4f46e5');
            $table->string('secondary_color')->default('#10b981');
            $table->string('sidebar_bg_color')->default('#ffffff');
            $table->string('sidebar_text_color')->default('#334155');
            $table->string('sidebar_active_color')->default('#4f46e5');
            $table->string('topbar_color')->default('#ffffff');
            $table->string('page_bg_color')->default('#f8fafc');
            $table->string('card_bg_color')->default('#ffffff');
            $table->string('text_color')->default('#1e293b');
            $table->unsignedTinyInteger('border_radius')->default(16);
            $table->enum('sidebar_size', ['compact', 'normal', 'wide'])->default('normal');
            $table->enum('theme_mode', ['light', 'dark', 'user_choice'])->default('light');
            $table->string('logo_full')->nullable();
            $table->string('logo_mini')->nullable();
            $table->string('favicon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_appearance_settings');
    }
};

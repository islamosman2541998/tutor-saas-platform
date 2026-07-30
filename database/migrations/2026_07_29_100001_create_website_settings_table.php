<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('site_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('teacher_image')->nullable();
            $table->text('short_description')->nullable();

            $table->string('primary_color')->default('#4f46e5');
            $table->string('secondary_color')->default('#10b981');
            $table->string('accent_color')->default('#f59e0b');
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#1e293b');
            $table->string('heading_color')->default('#0f172a');
            $table->string('button_color')->default('#4f46e5');
            $table->string('button_hover_color')->default('#4338ca');
            $table->string('navbar_color')->default('#ffffff');
            $table->string('footer_color')->default('#0f172a');

            $table->string('font_family')->default('Cairo');
            $table->unsignedTinyInteger('base_font_size')->default(16);
            $table->unsignedTinyInteger('heading_font_size')->default(32);
            $table->unsignedTinyInteger('border_radius')->default(12);
            $table->enum('button_style', ['rounded', 'pill', 'square'])->default('rounded');
            $table->boolean('card_shadow')->default(true);
            $table->enum('section_spacing', ['compact', 'normal', 'relaxed'])->default('normal');
            $table->enum('container_width', ['narrow', 'normal', 'wide'])->default('normal');

            // "Published/draft/maintenance" itself lives on tenants.website_status
            // (already part of the tenant record, set at registration) — these
            // are just the content shown *during* maintenance mode.
            $table->text('maintenance_message')->nullable();
            $table->string('maintenance_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};

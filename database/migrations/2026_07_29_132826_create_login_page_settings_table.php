<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_page_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('background_image')->nullable();
            $table->string('background_color')->default('#f8fafc');
            $table->boolean('overlay_enabled')->default(true);
            $table->decimal('overlay_opacity', 3, 2)->default(0.50);
            $table->enum('card_position', ['center', 'left', 'right'])->default('center');
            $table->string('card_bg_color')->default('#ffffff');
            $table->decimal('card_opacity', 3, 2)->default(1.00);
            $table->boolean('card_blur')->default(false);
            $table->unsignedTinyInteger('border_radius')->default(16);
            $table->enum('shadow_style', ['none', 'soft', 'medium', 'strong'])->default('medium');

            $table->string('title_color')->default('#0f172a');
            $table->string('text_color')->default('#475569');
            $table->string('label_color')->default('#334155');
            $table->string('input_bg_color')->default('#ffffff');
            $table->string('input_text_color')->default('#0f172a');
            $table->string('input_border_color')->default('#cbd5e1');
            $table->string('input_focus_color')->default('#4f46e5');
            $table->string('button_color')->default('#4f46e5');
            $table->string('button_text_color')->default('#ffffff');
            $table->string('button_hover_color')->default('#4338ca');

            $table->boolean('show_logo')->default(true);
            $table->string('welcome_title')->nullable();
            $table->string('welcome_description')->nullable();
            $table->string('side_image')->nullable();
            $table->boolean('remember_me_enabled')->default(true);
            $table->boolean('forgot_password_enabled')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_page_settings');
    }
};

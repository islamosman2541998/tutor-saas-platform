<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('teacher_name');
            $table->string('teacher_image')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->enum('website_status', ['draft', 'published', 'maintenance'])->default('draft');
            $table->unsignedBigInteger('current_academic_year_id')->nullable();
            $table->unsignedTinyInteger('setup_step')->default(1);
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('timezone')->default('Africa/Cairo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

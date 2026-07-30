<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedInteger('max_students')->nullable();
            $table->unsignedInteger('max_groups')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->boolean('website_enabled')->default(true);
            $table->boolean('custom_domain_enabled')->default(false);
            $table->boolean('excel_export_enabled')->default(true);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('advanced_reports_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained()->restrictOnDelete();
            $table->date('scheduled_date');
            $table->time('expected_start_time');
            $table->time('expected_end_time');
            $table->dateTime('actual_started_at')->nullable();
            $table->dateTime('actual_closed_at')->nullable();
            $table->string('title')->nullable();
            $table->string('lesson_topic')->nullable();
            $table->text('notes')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->dateTime('qr_expires_at')->nullable();
            $table->boolean('qr_is_active')->default(false);
            $table->boolean('attendance_is_locked')->default(false);
            $table->enum('status', ['scheduled', 'open', 'completed', 'cancelled'])->default('scheduled');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['group_id', 'scheduled_date', 'expected_start_time'], 'class_sessions_group_date_time_unique');
            $table->index(['tenant_id', 'group_id', 'scheduled_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};

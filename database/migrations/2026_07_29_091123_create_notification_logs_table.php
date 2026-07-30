<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            // Nullable (unlike Payment/MonthlyDue) — mirrors Activity's
            // tenant_id: not TenantScope-enforced, set explicitly by
            // whatever dispatches the notification, to leave room for
            // future central-platform notifications with no tenant at all.
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notification_type');
            $table->enum('channel', ['database', 'mail', 'whatsapp', 'sms']);
            $table->string('recipient');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('related_model_type')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['related_model_type', 'related_model_id']);
            $table->index(['notification_type', 'related_model_type', 'related_model_id'], 'notification_logs_type_related_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};

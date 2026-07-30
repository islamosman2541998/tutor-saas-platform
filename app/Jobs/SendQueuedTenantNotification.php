<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Notifications\TenantNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Does the actual channel send and is the single place that flips a
 * notification_logs row from 'pending' to 'sent'/'failed' — kept separate
 * from Laravel's own queued-notification machinery (rather than making
 * TenantNotification subclasses ShouldQueue themselves) so that outcome is
 * driven by a plain try/catch here instead of correlating Laravel's
 * NotificationSent/NotificationFailed events back to a specific log row.
 */
class SendQueuedTenantNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        protected int $logId,
        protected TenantNotification $notification,
        protected string $email,
    ) {}

    public function handle(): void
    {
        $log = NotificationLog::find($this->logId);

        if (! $log) {
            return;
        }

        try {
            Notification::route('mail', $this->email)->notify($this->notification);

            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => Str::limit($e->getMessage(), 500),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        NotificationLog::query()->whereKey($this->logId)->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => Str::limit($e->getMessage(), 500),
        ]);
    }
}

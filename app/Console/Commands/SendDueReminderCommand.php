<?php

namespace App\Console\Commands;

use App\Actions\Notifications\SendTenantNotificationAction;
use App\Models\MonthlyDue;
use App\Models\NotificationLog;
use App\Models\Tenant;
use App\Notifications\DueReminderNotification;
use App\Support\Tenancy\TenantContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-due-reminders')]
#[Description('Sends a reminder for dues that are overdue or due within the next 3 days, for every active tenant.')]
class SendDueReminderCommand extends Command
{
    public function handle(TenantContext $context, SendTenantNotificationAction $action): int
    {
        $sent = 0;
        $skipped = 0;

        foreach (Tenant::query()->where('status', 'active')->get() as $tenant) {
            $context->set($tenant);

            $dues = MonthlyDue::query()
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->where('due_date', '<=', now()->addDays(3)->toDateString())
                ->with('student')
                ->get();

            foreach ($dues as $due) {
                // One attempt per due per 20h, regardless of outcome — a
                // student with no contact info would otherwise get a fresh
                // "failed" log entry every single run.
                $recentlyAttempted = NotificationLog::query()
                    ->where('notification_type', 'due_reminder')
                    ->where('related_model_type', MonthlyDue::class)
                    ->where('related_model_id', $due->id)
                    ->where('created_at', '>=', now()->subHours(20))
                    ->exists();

                if ($recentlyAttempted) {
                    $skipped++;

                    continue;
                }

                $email = $due->student->email ?: $due->student->guardian_email;
                $phone = $due->student->phone ?: $due->student->guardian_phone;

                $action->execute(new DueReminderNotification($due), $due, $email, $phone);
                $sent++;
            }
        }

        $context->set(null);

        $this->info("Due reminders: {$sent} dispatched, {$skipped} skipped (already attempted within 20h).");

        return self::SUCCESS;
    }
}

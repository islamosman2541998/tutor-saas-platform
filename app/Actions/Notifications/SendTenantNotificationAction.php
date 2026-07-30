<?php

namespace App\Actions\Notifications;

use App\Jobs\SendQueuedTenantNotification;
use App\Models\NotificationLog;
use App\Models\Tenant;
use App\Notifications\TenantNotification;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

/**
 * The one place notifications get created and dispatched from. Students and
 * guardians have no login in the MVP, so "recipient" is always plain
 * contact info off the related model, not a Notifiable Eloquent user —
 * channel is picked by what contact info is actually available: email →
 * mail (the only channel really wired up), phone → whatsapp (logged as
 * failed, not yet configured, but the recipient/channel are recorded
 * correctly for whenever that integration exists), neither → database
 * (logged as failed: no reachable contact info at all).
 */
class SendTenantNotificationAction
{
    public function execute(TenantNotification $notification, Model $relatedModel, ?string $email, ?string $phone = null): NotificationLog
    {
        $log = NotificationLog::create([
            'tenant_id' => $this->resolveTenantId($relatedModel),
            'notification_type' => $notification->type(),
            'channel' => $email ? 'mail' : ($phone ? 'whatsapp' : 'database'),
            'recipient' => $email ?: ($phone ?: '—'),
            'status' => 'pending',
            'related_model_type' => $relatedModel::class,
            'related_model_id' => $relatedModel->getKey(),
        ]);

        if ($email) {
            SendQueuedTenantNotification::dispatch($log->id, $notification, $email);

            return $log;
        }

        $log->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $phone
                ? 'قناة واتساب غير مفعّلة على المنصة بعد.'
                : 'لا توجد وسيلة تواصل متاحة لهذا المستلم (لا بريد إلكتروني ولا رقم هاتف).',
        ]);

        return $log->fresh();
    }

    protected function resolveTenantId(Model $relatedModel): ?int
    {
        if ($relatedModel instanceof Tenant) {
            return $relatedModel->id;
        }

        if (isset($relatedModel->tenant_id)) {
            return $relatedModel->tenant_id;
        }

        return app(TenantContext::class)->id();
    }
}

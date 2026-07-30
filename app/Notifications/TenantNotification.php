<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Base for every notification type in the platform. Deliberately does NOT
 * implement ShouldQueue itself — students/guardians aren't Notifiable
 * Eloquent models (no login in the MVP), so these are always sent via
 * Laravel's on-demand notification routing from inside
 * SendQueuedTenantNotification, which is what actually goes through the
 * queue and updates the matching notification_logs row.
 */
abstract class TenantNotification extends Notification
{
    /**
     * Short machine-readable identifier stored as notification_logs.notification_type
     * (e.g. "payment_received"), not the fully-qualified class name.
     */
    abstract public function type(): string;
}

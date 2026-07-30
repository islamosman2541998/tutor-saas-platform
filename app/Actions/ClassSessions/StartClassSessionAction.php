<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Str;

class StartClassSessionAction
{
    /**
     * Default QR validity window. There's no tenant_settings table yet to
     * make this configurable per tenant — see the Settings module.
     */
    protected const DEFAULT_QR_MINUTES = 15;

    public function execute(ClassSession $session, ?int $qrMinutes = null): ClassSession
    {
        if ($session->status !== 'scheduled') {
            throw new CannotPerformActionException('لا يمكن بدء حصة إلا وهي في حالة "مجدولة".');
        }

        // status is fillable, but the QR/timing fields are deliberately
        // excluded from ClassSession's fillable list (never settable from a
        // form) — forceFill is required since this is one of the few
        // legitimate places they're allowed to change.
        $session->forceFill([
            'status' => 'open',
            'actual_started_at' => now(),
            'qr_token' => Str::random(64),
            'qr_expires_at' => now()->addMinutes($qrMinutes ?? self::DEFAULT_QR_MINUTES),
            'qr_is_active' => true,
        ])->save();

        TenantActivity::log('بدء حصة', $session, ['date' => $session->scheduled_date->toDateString()]);

        return $session->fresh();
    }
}

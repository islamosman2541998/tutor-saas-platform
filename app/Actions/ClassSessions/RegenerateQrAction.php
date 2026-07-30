<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Str;

/**
 * Invalidates the current QR token and issues a brand-new one — anyone
 * still holding the old QR image (a photo, a printed copy, a stale browser
 * tab) can no longer use it, since the token itself has changed.
 */
class RegenerateQrAction
{
    public function execute(ClassSession $session, ?int $qrMinutes = 15): ClassSession
    {
        if ($session->status !== 'open') {
            throw new CannotPerformActionException('لا يمكن إعادة توليد رمز الحضور إلا لحصة مفتوحة.');
        }

        $session->forceFill([
            'qr_token' => Str::random(64),
            'qr_expires_at' => now()->addMinutes($qrMinutes),
            'qr_is_active' => true,
        ])->save();

        TenantActivity::log('إعادة توليد رمز الحضور', $session, ['date' => $session->scheduled_date->toDateString()]);

        return $session->fresh();
    }
}

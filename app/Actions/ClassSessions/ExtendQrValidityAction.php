<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;

class ExtendQrValidityAction
{
    public function execute(ClassSession $session, int $extraMinutes): ClassSession
    {
        if ($session->status !== 'open') {
            throw new CannotPerformActionException('لا يمكن تمديد رمز حضور إلا لحصة مفتوحة.');
        }

        $base = $session->qr_expires_at && $session->qr_expires_at->isFuture()
            ? $session->qr_expires_at
            : now();

        $session->forceFill([
            'qr_expires_at' => $base->addMinutes($extraMinutes),
            'qr_is_active' => true,
        ])->save();

        TenantActivity::log('تمديد صلاحية رمز الحضور', $session, ['extra_minutes' => $extraMinutes]);

        return $session->fresh();
    }
}

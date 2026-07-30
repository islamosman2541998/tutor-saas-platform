<?php

namespace App\Actions\ClassSessions;

use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;

/**
 * Stops new check-ins without closing the session itself — the teacher can
 * keep taking manual attendance afterward.
 */
class StopQrAction
{
    public function execute(ClassSession $session): ClassSession
    {
        $session->forceFill(['qr_is_active' => false])->save();

        TenantActivity::log('إيقاف رمز الحضور', $session, ['date' => $session->scheduled_date->toDateString()]);

        return $session->fresh();
    }
}

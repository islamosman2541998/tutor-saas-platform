<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;

class CancelSessionAction
{
    public function execute(ClassSession $session, string $reason): ClassSession
    {
        if ($session->status === 'completed') {
            throw new CannotPerformActionException('لا يمكن إلغاء حصة مكتملة بالفعل.');
        }

        $session->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        TenantActivity::log('إلغاء حصة', $session, [
            'date' => $session->scheduled_date->toDateString(),
            'reason' => $reason,
        ]);

        return $session->fresh();
    }
}

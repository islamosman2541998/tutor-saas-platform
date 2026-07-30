<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;

class CloseClassSessionAction
{
    public function execute(ClassSession $session): ClassSession
    {
        if ($session->status !== 'open') {
            throw new CannotPerformActionException('لا يمكن إغلاق إلا حصة مفتوحة.');
        }

        $session->forceFill([
            'status' => 'completed',
            'actual_closed_at' => now(),
            'qr_is_active' => false,
            'attendance_is_locked' => true,
            'closed_by' => Auth::id(),
        ])->save();

        TenantActivity::log('إغلاق حصة', $session, ['date' => $session->scheduled_date->toDateString()]);

        return $session->fresh();
    }
}

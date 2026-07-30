<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\ClassSession;
use App\Support\Activity\TenantActivity;

/**
 * Only a still-"scheduled" session's date/time may change — once it's been
 * opened (QR generated, attendance in progress) or completed, its record
 * becomes historical and must stay exactly as it happened.
 */
class UpdateSessionAction
{
    public function execute(ClassSession $session, array $data): ClassSession
    {
        if ($session->status !== 'scheduled') {
            throw new CannotPerformActionException('لا يمكن تعديل موعد حصة بدأت أو اكتملت أو أُلغيت.');
        }

        $session->update($data);

        TenantActivity::log('تعديل موعد حصة', $session, ['date' => $session->scheduled_date->toDateString()]);

        return $session->fresh();
    }
}

<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Support\Activity\TenantActivity;

class WithdrawEnrollmentAction
{
    public function execute(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        $enrollment->update([
            'status' => 'withdrawn',
            'left_at' => now()->toDateString(),
            'withdrawal_reason' => $reason,
        ]);

        TenantActivity::log('انسحاب طالب من مجموعة', $enrollment, [
            'student' => $enrollment->student->name,
            'group' => $enrollment->group->name,
            'reason' => $reason,
        ]);

        return $enrollment->fresh();
    }
}

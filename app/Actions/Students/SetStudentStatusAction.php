<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Support\Activity\TenantActivity;

class SetStudentStatusAction
{
    public function execute(Student $student, string $status): Student
    {
        $previous = $student->status;

        $student->update(['status' => $status]);

        if ($previous !== $status) {
            TenantActivity::log('تغيير حالة الطالب', $student, ['from' => $previous, 'to' => $status]);
        }

        return $student->fresh();
    }
}

<?php

namespace App\Actions\Enrollments;

use App\Models\RegistrationRequest;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;

class RejectRegistrationRequestAction
{
    public function execute(RegistrationRequest $request): RegistrationRequest
    {
        // processed_by / processed_at are deliberately excluded from the
        // model's fillable list — forceFill is required here since this is
        // the one legitimate place they're set.
        $request->forceFill([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ])->save();

        TenantActivity::log('رفض طلب تسجيل أونلاين', $request, [
            'student_name' => $request->student_name,
            'group' => $request->group->name,
        ]);

        return $request;
    }
}

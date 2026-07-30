<?php

namespace App\Actions\Enrollments;

use App\Models\Group;
use App\Models\RegistrationRequest;
use App\Support\Activity\TenantActivity;
use Illuminate\Validation\ValidationException;

/**
 * Records an anonymous visitor's interest in a group from the public
 * marketing site — no Student exists yet, that only happens if/when a
 * teacher approves the request (see ApproveRegistrationRequestAction).
 */
class SubmitRegistrationRequestAction
{
    public function execute(Group $group, array $data): RegistrationRequest
    {
        if (! $group->allow_online_registration) {
            throw ValidationException::withMessages([
                'group_id' => 'التسجيل الأونلاين غير متاح لهذه المجموعة.',
            ]);
        }

        $request = RegistrationRequest::query()->create([
            'group_id' => $group->id,
            'student_name' => $data['student_name'],
            'phone' => $data['phone'],
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        TenantActivity::log('طلب تسجيل جديد من الموقع', $request, [
            'student_name' => $request->student_name,
            'group' => $group->name,
        ]);

        return $request;
    }
}

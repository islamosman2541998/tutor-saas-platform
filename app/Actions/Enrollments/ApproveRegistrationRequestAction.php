<?php

namespace App\Actions\Enrollments;

use App\Actions\Students\CreateStudentAction;
use App\Models\RegistrationRequest;
use App\Models\Student;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Turns a pending online registration request into a real Student +
 * Enrollment. Reuses an existing Student (matched by phone/guardian phone)
 * instead of creating a duplicate if the family already has a record, and
 * defers to EnrollStudentAction's own full-group handling — a request
 * approved after the group filled up quietly becomes a waiting-list entry
 * instead of failing outright.
 */
class ApproveRegistrationRequestAction
{
    public function __construct(
        protected CreateStudentAction $createStudent,
        protected EnrollStudentAction $enrollStudent,
    ) {}

    public function execute(RegistrationRequest $request): array
    {
        return DB::transaction(function () use ($request) {
            $student = Student::query()
                ->where(function ($q) use ($request) {
                    $q->where('phone', $request->phone);

                    if ($request->guardian_phone) {
                        $q->orWhere('guardian_phone', $request->guardian_phone);
                    }
                })
                ->first();

            if (! $student) {
                $student = $this->createStudent->execute([
                    'name' => $request->student_name,
                    'phone' => $request->phone,
                    'guardian_phone' => $request->guardian_phone,
                    'notes' => $request->notes,
                ]);
            }

            $result = $this->enrollStudent->execute($student, $request->group, []);

            // student_id / enrollment_id / processed_by / processed_at are
            // deliberately excluded from the model's fillable list (never
            // settable from a form) — forceFill is required here since this
            // is the one legitimate place they're set.
            $request->forceFill([
                'status' => 'approved',
                'student_id' => $student->id,
                'enrollment_id' => $result['enrollment']?->id,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ])->save();

            TenantActivity::log('قبول طلب تسجيل أونلاين', $request, [
                'student' => $student->name,
                'group' => $request->group->name,
                'result' => $result['type'],
            ]);

            return $result;
        });
    }
}

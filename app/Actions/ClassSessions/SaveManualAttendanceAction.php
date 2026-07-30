<?php

namespace App\Actions\ClassSessions;

use App\Exceptions\CannotPerformActionException;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Bulk-saves the teacher's manual attendance sheet for a session. Never
 * touches a student's registration_method once set — a record that started
 * as a QR check-in stays tagged "qr" even if its status is later corrected
 * here, because that's still a true fact about how the student showed up.
 * Editing an existing QR-born record is logged explicitly, since attendance
 * history should show it wasn't the student's own scan that produced the
 * final state.
 */
class SaveManualAttendanceAction
{
    /**
     * @param  array<int, string>  $statuses  student_id => 'present'|'absent'|'late'|'excused'|'unmarked'
     */
    public function execute(ClassSession $session, array $statuses): void
    {
        if ($session->attendance_is_locked && ! Auth::user()->can('attendance.unlock')) {
            throw new CannotPerformActionException('الحضور مقفل لهذه الحصة. التعديل يحتاج صلاحية خاصة.');
        }

        DB::transaction(function () use ($session, $statuses) {
            foreach ($statuses as $studentId => $status) {
                if ($status === 'unmarked') {
                    continue;
                }

                $enrollment = Enrollment::query()
                    ->where('student_id', $studentId)
                    ->where('group_id', $session->group_id)
                    ->where('status', 'active')
                    ->first();

                if (! $enrollment) {
                    continue;
                }

                $existing = AttendanceRecord::query()
                    ->where('class_session_id', $session->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    if ($existing->status === $status) {
                        continue;
                    }

                    $wasQr = $existing->registration_method === 'qr';
                    $previousStatus = $existing->status;

                    $existing->update(['status' => $status, 'registered_by' => Auth::id()]);

                    if ($wasQr) {
                        TenantActivity::log('تعديل حضور مُسجَّل عبر QR', $existing, [
                            'student_id' => $studentId,
                            'from' => $previousStatus,
                            'to' => $status,
                        ]);
                    }
                } else {
                    AttendanceRecord::query()->create([
                        'class_session_id' => $session->id,
                        'student_id' => $studentId,
                        'enrollment_id' => $enrollment->id,
                        'status' => $status,
                        'registration_method' => 'manual',
                        'checked_in_at' => now(),
                        'registered_by' => Auth::id(),
                    ]);
                }
            }
        });

        TenantActivity::log('حفظ الحضور اليدوي', $session, ['date' => $session->scheduled_date->toDateString()]);
    }
}

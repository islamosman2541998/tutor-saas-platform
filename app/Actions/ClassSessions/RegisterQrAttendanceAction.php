<?php

namespace App\Actions\ClassSessions;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\TenantContext;
use Spatie\Permission\PermissionRegistrar;

/**
 * The public, unauthenticated QR check-in flow. Verification happens in a
 * strict order — everything up to "student identified" fails with the
 * exact same generic message so a wrong student code and a wrong phone
 * suffix are indistinguishable from outside (no oracle for guessing).
 * Only once the student is genuinely confirmed do later failures (not
 * enrolled, already checked in) get specific, helpful messages.
 */
class RegisterQrAttendanceAction
{
    protected const GENERIC_FAILURE = 'البيانات المدخلة غير صحيحة.';

    /**
     * @return array{success: bool, message: string}
     */
    public function execute(string $token, string $studentCode, string $last4Phone, ?string $ip, ?string $userAgent): array
    {
        $session = ClassSession::withoutGlobalScope(TenantScope::class)
            ->where('qr_token', $token)
            ->first();

        if (! $session) {
            return $this->fail('رابط الحضور غير صالح.');
        }

        if ($session->status !== 'open' || ! $session->qr_is_active) {
            return $this->fail('انتهت صلاحية رمز الحضور لهذه الحصة أو لم تبدأ بعد.');
        }

        if ($session->qr_expires_at && $session->qr_expires_at->isPast()) {
            return $this->fail('انتهت صلاحية رمز الحضور. اطلب من المدرس رمزًا جديدًا.');
        }

        // Everything from here on operates within the session's tenant.
        app(TenantContext::class)->set($session->tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($session->tenant_id);

        $student = Student::query()
            ->where('student_code', $studentCode)
            ->first();

        if (! $student || ! $this->last4Match($student, $last4Phone)) {
            return $this->fail(self::GENERIC_FAILURE);
        }

        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('group_id', $session->group_id)
            ->where('status', 'active')
            ->first();

        if (! $enrollment) {
            return $this->fail('أنت غير مشترك في هذه المجموعة.');
        }

        $alreadyRecorded = AttendanceRecord::query()
            ->where('class_session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyRecorded) {
            return $this->fail('تم تسجيل حضورك بالفعل لهذه الحصة.');
        }

        AttendanceRecord::query()->create([
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'present',
            'registration_method' => 'qr',
            'checked_in_at' => now(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        return ['success' => true, 'message' => "تم تسجيل حضورك بنجاح، أهلًا {$student->name}."];
    }

    protected function last4Match(Student $student, string $last4): bool
    {
        foreach ([$student->phone, $student->guardian_phone] as $phone) {
            if ($phone && str_ends_with(preg_replace('/\D/', '', $phone), $last4)) {
                return true;
            }
        }

        return false;
    }

    protected function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}

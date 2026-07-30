<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use App\Models\WaitingListEntry;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Enrolls a student into a group, or — if the group is already at capacity
 * — creates a waiting list entry instead. The capacity check and the write
 * happen inside one locked transaction so two enrollments submitted at the
 * same instant can't both slip in past a group's last open seat.
 */
class EnrollStudentAction
{
    /**
     * @return array{type: 'enrolled'|'waitlisted', enrollment: ?Enrollment, waiting_entry: ?WaitingListEntry}
     */
    public function execute(Student $student, Group $group, array $data): array
    {
        return DB::transaction(function () use ($student, $group, $data) {
            $group = Group::query()->whereKey($group->id)->lockForUpdate()->firstOrFail();

            $alreadyActive = Enrollment::query()
                ->where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyActive) {
                throw ValidationException::withMessages([
                    'group_id' => 'الطالب مشترك بالفعل في هذه المجموعة.',
                ]);
            }

            if ($group->isFull()) {
                $entry = WaitingListEntry::query()->create([
                    'student_id' => $student->id,
                    'group_id' => $group->id,
                    'requested_at' => now(),
                    'notes' => $data['notes'] ?? null,
                ]);

                TenantActivity::log('إضافة طالب لقائمة الانتظار', $entry, [
                    'student' => $student->name,
                    'group' => $group->name,
                ]);

                return ['type' => 'waitlisted', 'enrollment' => null, 'waiting_entry' => $entry];
            }

            $finalPrice = $this->calculateFinalPrice(
                (float) ($data['custom_monthly_price'] ?? $group->monthly_price),
                $data['discount_type'] ?? 'none',
                (float) ($data['discount_value'] ?? 0),
            );

            $enrollment = Enrollment::query()->forceCreate([
                'student_id' => $student->id,
                'group_id' => $group->id,
                'academic_year_id' => $group->academic_year_id,
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
                'default_monthly_price' => $group->monthly_price,
                'custom_monthly_price' => $data['custom_monthly_price'] ?? null,
                'discount_type' => $data['discount_type'] ?? 'none',
                'discount_value' => $data['discount_value'] ?? 0,
                'final_monthly_price' => $finalPrice,
                'payment_due_day' => $data['payment_due_day'] ?? null,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            TenantActivity::log('اشتراك طالب في مجموعة', $enrollment, [
                'student' => $student->name,
                'group' => $group->name,
            ]);

            return ['type' => 'enrolled', 'enrollment' => $enrollment, 'waiting_entry' => null];
        });
    }

    protected function calculateFinalPrice(float $base, string $discountType, float $discountValue): float
    {
        $final = match ($discountType) {
            'fixed' => $base - $discountValue,
            'percentage' => $base - ($base * $discountValue / 100),
            default => $base,
        };

        return max(0, round($final, 2));
    }
}

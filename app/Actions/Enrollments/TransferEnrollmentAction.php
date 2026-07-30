<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Models\Group;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\DB;

/**
 * Moves a student from their current group to a different one: the old
 * enrollment is closed with status "transferred" (not "withdrawn" — it's a
 * distinct reason kept for reporting) and a fresh enrollment is opened via
 * EnrollStudentAction, which still enforces capacity/waiting-list rules on
 * the destination group exactly as a normal new enrollment would.
 */
class TransferEnrollmentAction
{
    public function __construct(protected EnrollStudentAction $enrollStudent) {}

    public function execute(Enrollment $enrollment, Group $toGroup, array $data): array
    {
        return DB::transaction(function () use ($enrollment, $toGroup, $data) {
            $fromGroup = $enrollment->group;

            $enrollment->update([
                'status' => 'transferred',
                'left_at' => now()->toDateString(),
            ]);

            $result = $this->enrollStudent->execute($enrollment->student, $toGroup, $data);

            TenantActivity::log('نقل طالب بين مجموعات', $enrollment->student, [
                'from_group' => $fromGroup->name,
                'to_group' => $toGroup->name,
            ]);

            return $result;
        });
    }
}

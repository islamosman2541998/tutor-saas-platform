<?php

namespace App\Actions\Groups;

use App\Models\Group;
use App\Support\Activity\TenantActivity;

/**
 * No "has active students" guard yet — Enrollments doesn't exist as a
 * module yet. Once it does, block deleting a group with active enrollments
 * the same way DeleteStageAction blocks deleting a stage with grades.
 */
class DeleteGroupAction
{
    public function execute(Group $group): void
    {
        $group->schedules()->delete();
        $group->delete();

        TenantActivity::log('حذف مجموعة', $group, ['name' => $group->name]);
    }
}

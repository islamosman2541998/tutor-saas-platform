<?php

namespace App\Actions\ClassSessions;

use App\Models\ClassSession;
use App\Models\Group;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\Auth;

class CreateManualSessionAction
{
    public function execute(Group $group, array $data): ClassSession
    {
        $session = ClassSession::query()->forceCreate([
            'tenant_id' => $group->tenant_id,
            'group_id' => $group->id,
            'scheduled_date' => $data['scheduled_date'],
            'expected_start_time' => $data['expected_start_time'],
            'expected_end_time' => $data['expected_end_time'],
            'title' => $data['title'] ?? null,
            'lesson_topic' => $data['lesson_topic'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled',
            'created_by' => Auth::id(),
        ]);

        TenantActivity::log('إنشاء حصة يدويًا', $session, [
            'group' => $group->name,
            'date' => $session->scheduled_date->toDateString(),
        ]);

        return $session;
    }
}

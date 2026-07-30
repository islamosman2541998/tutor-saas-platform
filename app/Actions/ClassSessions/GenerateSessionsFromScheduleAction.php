<?php

namespace App\Actions\ClassSessions;

use App\Models\ClassSession;
use App\Models\Group;
use App\Support\Activity\TenantActivity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

/**
 * Creates a ClassSession row for every date in [from, to] whose weekday
 * matches one of the group's active weekly schedule slots. Safe to run
 * repeatedly for overlapping ranges — a session is only created if none
 * already exists for that exact group + date + start_time combination
 * (mirrors the DB unique index, which is the real guarantee; this check
 * just avoids relying on catching a constraint-violation exception).
 */
class GenerateSessionsFromScheduleAction
{
    public function execute(Group $group, CarbonImmutable $from, CarbonImmutable $to): int
    {
        $schedules = $group->schedules()->where('is_active', true)->get();

        if ($schedules->isEmpty()) {
            return 0;
        }

        $existing = ClassSession::query()
            ->where('group_id', $group->id)
            ->whereBetween('scheduled_date', [$from->toDateString(), $to->toDateString()])
            ->get(['scheduled_date', 'expected_start_time'])
            ->map(fn ($s) => $s->scheduled_date->toDateString().'|'.$s->expected_start_time)
            ->flip();

        $created = 0;

        for ($date = $from; $date->lte($to); $date = $date->addDay()) {
            foreach ($schedules as $schedule) {
                if ($date->dayOfWeek !== $schedule->day_of_week) {
                    continue;
                }

                $key = $date->toDateString().'|'.$schedule->start_time;

                if ($existing->has($key)) {
                    continue;
                }

                ClassSession::query()->forceCreate([
                    'tenant_id' => $group->tenant_id,
                    'group_id' => $group->id,
                    'scheduled_date' => $date->toDateString(),
                    'expected_start_time' => $schedule->start_time,
                    'expected_end_time' => $schedule->end_time,
                    'status' => 'scheduled',
                    'created_by' => Auth::id(),
                ]);

                $created++;
            }
        }

        if ($created > 0) {
            TenantActivity::log('توليد حصص من الجدول الأسبوعي', $group, [
                'group' => $group->name,
                'count' => $created,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]);
        }

        return $created;
    }
}

<?php

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\GroupSchedule;
use App\Support\Activity\TenantActivity;

/**
 * Saving a schedule slot never blocks on a location conflict — the spec
 * calls for a *warning*, not a hard stop (an Assistant might legitimately
 * run a second group in the same room back-to-back, or the teacher knows
 * the overlap is fine). We just tell the caller what we found and let them
 * decide.
 */
class SaveGroupScheduleAction
{
    public function execute(Group $group, array $data, ?GroupSchedule $existing = null): array
    {
        $effectiveLocationId = $data['location_id'] ?: $group->location_id;

        $conflict = $this->findConflict(
            $group,
            $effectiveLocationId,
            (int) $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $existing?->id,
        );

        if ($existing) {
            $existing->update($data);
            $schedule = $existing->fresh();
        } else {
            $schedule = $group->schedules()->create($data);
        }

        TenantActivity::log('حفظ موعد مجموعة', $group, [
            'day' => $schedule->dayLabel(),
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
        ]);

        return [
            'schedule' => $schedule,
            'conflict_warning' => $conflict
                ? "تعارض محتمل: مجموعة \"{$conflict->group->name}\" لها موعد في نفس المكان والوقت تقريبًا."
                : null,
        ];
    }

    protected function findConflict(Group $group, ?int $locationId, int $dayOfWeek, string $startTime, string $endTime, ?int $excludeScheduleId): ?GroupSchedule
    {
        if (! $locationId) {
            return null;
        }

        return GroupSchedule::query()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('group_id', '!=', $group->id)
            ->when($excludeScheduleId, fn ($q) => $q->where('id', '!=', $excludeScheduleId))
            ->with('group')
            ->get()
            ->first(function (GroupSchedule $schedule) use ($locationId, $startTime, $endTime) {
                $scheduleLocationId = $schedule->location_id ?? $schedule->group->location_id;

                return $scheduleLocationId === $locationId
                    && $startTime < $schedule->end_time
                    && $schedule->start_time < $endTime;
            });
    }
}

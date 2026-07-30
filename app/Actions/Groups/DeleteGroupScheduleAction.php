<?php

namespace App\Actions\Groups;

use App\Models\GroupSchedule;
use App\Support\Activity\TenantActivity;

class DeleteGroupScheduleAction
{
    public function execute(GroupSchedule $schedule): void
    {
        $group = $schedule->group;

        $schedule->delete();

        TenantActivity::log('حذف موعد مجموعة', $group, [
            'day' => $schedule->dayLabel(),
            'start_time' => $schedule->start_time,
        ]);
    }
}

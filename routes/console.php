<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generates the coming month's dues a few days early so teachers can review
// them before the month actually starts — re-running this (or the manual
// "توليد" button) later in the month is safe, see GenerateMonthlyDuesAction.
Schedule::command('dues:generate')->monthlyOn(1, '02:00');

// Runs daily well after the dues-generation window so a due created
// overnight on the 1st can still be picked up the same day if it's already
// due soon — see SendDueReminderCommand's own 20h per-due cooldown for why
// running this more than once a day is still safe.
Schedule::command('notifications:send-due-reminders')->dailyAt('08:00');

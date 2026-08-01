<?php

namespace Database\Seeders;

use App\Actions\Billing\GenerateMonthlyDuesAction;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Support\Activity\TenantActivity;
use App\Support\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tops up operational data (sessions, attendance, enrollments, dues,
 * payments) for the last 6 months — including the current, still-in-progress
 * one — for every tenant that already has active students/groups. Every
 * dashboard chart/stat (TenantDashboardIndex) reads exactly this 6-month
 * window, so whenever the calendar rolls into a new month with nothing in it
 * yet, the dashboard goes back to showing zeros until this is re-run.
 *
 * Safe to run repeatedly: sessions are firstOrCreate'd on their unique key,
 * monthly dues go through GenerateMonthlyDuesAction (idempotent by design),
 * and a payment is only ever recorded against a due that's still unpaid at
 * the moment this runs — so re-running mid-month just fills in what's
 * missing rather than duplicating what's already there.
 */
class OperationalDemoDataSeeder extends Seeder
{
    protected const MONTHS_BACK = 5; // + the current month = 6 total

    protected const SESSIONS_PER_GROUP_PER_MONTH = 3;

    protected array $paymentMethods = ['cash', 'bank_transfer', 'wallet', 'card'];

    protected int $paymentMethodCursor = 0;

    public function run(GenerateMonthlyDuesAction $generateDues): void
    {
        $tenants = Tenant::query()->whereNotNull('owner_user_id')->get();

        foreach ($tenants as $tenant) {
            app(TenantContext::class)->set($tenant);

            $hasData = Student::query()->where('status', 'active')->exists()
                && Group::query()->where('status', 'active')->exists();

            if ($hasData) {
                $this->seedForTenant($tenant, $generateDues);
            }

            app(TenantContext::class)->set(null);
        }
    }

    protected function seedForTenant(Tenant $tenant, GenerateMonthlyDuesAction $generateDues): void
    {
        $students = Student::query()->where('status', 'active')->get();
        $groups = Group::query()->where('status', 'active')->with('schedules')->get();

        $counts = ['sessions' => 0, 'attendance' => 0, 'enrollments' => 0, 'dues' => 0, 'payments' => 0];

        DB::transaction(function () use ($tenant, $students, $groups, $generateDues, &$counts) {
            $this->topUpEnrollments($students, $groups, $counts);

            $today = now();

            for ($monthsAgo = self::MONTHS_BACK; $monthsAgo >= 0; $monthsAgo--) {
                $monthDate = $today->copy()->startOfMonth()->subMonths($monthsAgo);
                $isCurrentMonth = $monthsAgo === 0;

                // Sessions dated after today never happened yet — attendance
                // only makes sense up to whichever came first, today or the
                // month's last day.
                $lastUsableDay = $isCurrentMonth ? $today->day : $monthDate->daysInMonth;

                foreach ($groups as $group) {
                    $this->seedSessionsForGroupMonth($group, $monthDate, $lastUsableDay, $isCurrentMonth, $counts);
                }

                $result = $generateDues->execute($monthDate->month, $monthDate->year, true, null);
                $counts['dues'] += $result['created'];

                $this->recordPaymentsForMonth($monthDate, $lastUsableDay, $counts);
            }
        });

        TenantActivity::log('تعبئة بيانات تشغيلية (سيدر)', $tenant, $counts);
    }

    /**
     * A handful of students without an active enrollment in a given group
     * get one, backdated within the current month — keeps "اشتراكات جديدة"
     * from going flat-zero the moment a new month starts.
     */
    protected function topUpEnrollments($students, $groups, array &$counts): void
    {
        if ($groups->isEmpty()) {
            return;
        }

        $candidates = $students->shuffle()->take(min(4, $students->count()));

        foreach ($candidates as $student) {
            $group = $groups->random();

            $alreadyEnrolled = Enrollment::query()
                ->where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyEnrolled) {
                continue;
            }

            $price = (float) $group->monthly_price;

            Enrollment::query()->forceCreate([
                'student_id' => $student->id,
                'group_id' => $group->id,
                'academic_year_id' => $group->academic_year_id,
                'joined_at' => now()->startOfMonth()->addDays(random_int(0, min(now()->day - 1, 5))),
                'default_monthly_price' => $price,
                'custom_monthly_price' => null,
                'discount_type' => 'none',
                'discount_value' => 0,
                'final_monthly_price' => $price,
                'status' => 'active',
            ]);

            $counts['enrollments']++;
        }
    }

    protected function seedSessionsForGroupMonth(Group $group, Carbon $monthDate, int $lastUsableDay, bool $isCurrentMonth, array &$counts): void
    {
        $schedule = $group->schedules->firstWhere('is_active', true) ?? $group->schedules->first();
        $weekday = $schedule?->day_of_week ?? 1; // default: Monday
        $startTime = $schedule?->start_time ?? '16:00:00';
        $endTime = $schedule?->end_time ?? '17:30:00';

        $dates = $this->weekdayDatesInMonth($monthDate, $weekday);

        // Add 1-2 upcoming sessions past "today" for the current month so
        // the groups/sessions screens don't look like everything stopped —
        // these stay 'scheduled', no attendance.
        $upcoming = $isCurrentMonth
            ? array_values(array_filter($dates, fn (Carbon $d) => $d->day > $lastUsableDay))
            : [];
        $past = array_values(array_filter($dates, fn (Carbon $d) => $d->day <= $lastUsableDay));

        // Early in a fresh month (even day 1), the group's weekly slot may
        // not have occurred yet — force one session on the most recent
        // usable day anyway, or the dashboard stays at zero until it does.
        if ($isCurrentMonth && $past === []) {
            $past[] = $monthDate->copy()->day($lastUsableDay);
        }

        $activeEnrollments = Enrollment::query()
            ->where('group_id', $group->id)
            ->where('status', 'active')
            ->get()
            ->keyBy('student_id');

        foreach (array_slice($past, 0, self::SESSIONS_PER_GROUP_PER_MONTH) as $date) {
            $session = ClassSession::query()->firstOrCreate(
                [
                    'group_id' => $group->id,
                    'scheduled_date' => $date->toDateString(),
                    'expected_start_time' => $startTime,
                ],
                [
                    'expected_end_time' => $endTime,
                    'status' => 'completed',
                    'actual_started_at' => $date->copy()->setTimeFromTimeString($startTime),
                    'actual_closed_at' => $date->copy()->setTimeFromTimeString($endTime),
                ]
            );

            if ($session->wasRecentlyCreated) {
                $counts['sessions']++;
                $this->seedAttendanceForSession($session, $activeEnrollments, $date, $startTime, $counts);
            }
        }

        foreach (array_slice($upcoming, 0, 2) as $date) {
            $session = ClassSession::query()->firstOrCreate(
                [
                    'group_id' => $group->id,
                    'scheduled_date' => $date->toDateString(),
                    'expected_start_time' => $startTime,
                ],
                ['expected_end_time' => $endTime, 'status' => 'scheduled']
            );

            if ($session->wasRecentlyCreated) {
                $counts['sessions']++;
            }
        }
    }

    protected function seedAttendanceForSession(ClassSession $session, $activeEnrollments, Carbon $date, string $startTime, array &$counts): void
    {
        $checkedInAt = $date->copy()->setTimeFromTimeString($startTime);

        foreach ($activeEnrollments as $studentId => $enrollment) {
            // A student's enrollment might postdate this particular session.
            if ($enrollment->joined_at->gt($date)) {
                continue;
            }

            $roll = random_int(1, 100);
            $status = match (true) {
                $roll <= 82 => 'present',
                $roll <= 90 => 'late',
                $roll <= 96 => 'absent',
                default => 'excused',
            };

            AttendanceRecord::query()->firstOrCreate(
                ['class_session_id' => $session->id, 'student_id' => $studentId],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => $status,
                    'registration_method' => 'system',
                    'checked_in_at' => in_array($status, ['present', 'late'], true) ? $checkedInAt : null,
                ]
            );

            $counts['attendance']++;
        }
    }

    /**
     * Settles most of a billing month's dues so revenue/payment-method
     * charts have something to show — a due only gets paid if it's still
     * unpaid right now, so re-running this never double-pays one.
     */
    protected function recordPaymentsForMonth(Carbon $monthDate, int $lastUsableDay, array &$counts): void
    {
        $dues = MonthlyDue::query()
            ->where('billing_month', $monthDate->month)
            ->where('billing_year', $monthDate->year)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get();

        $receiptCursor = Payment::query()->count();

        foreach ($dues as $due) {
            $roll = random_int(1, 100);

            // ~15% stay unpaid/overdue on purpose — an all-green ledger
            // wouldn't look real, and the dashboard's "متأخرات" tile needs
            // something to report too.
            if ($roll > 85) {
                continue;
            }

            $amount = $roll > 75
                ? round((float) $due->remaining_amount / 2, 2) // partial
                : (float) $due->remaining_amount; // full

            if ($amount <= 0) {
                continue;
            }

            $paidDay = min($lastUsableDay, random_int(1, max(1, $lastUsableDay)));
            $paidAt = $monthDate->copy()->addDays($paidDay - 1)->setTime(random_int(9, 20), random_int(0, 59));

            $receiptCursor++;
            $method = $this->paymentMethods[$this->paymentMethodCursor++ % count($this->paymentMethods)];

            Payment::query()->forceCreate([
                'student_id' => $due->student_id,
                'enrollment_id' => $due->enrollment_id,
                'monthly_due_id' => $due->id,
                'receipt_number' => 'RCPT-'.str_pad((string) $receiptCursor, 6, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'payment_method' => $method,
                'paid_at' => $paidAt,
                'status' => 'completed',
            ]);

            $newPaid = round((float) $due->paid_amount + $amount, 2);
            $newRemaining = max(0, round((float) $due->final_amount - $newPaid, 2));

            $due->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => $newRemaining,
                'status' => $newRemaining <= 0 ? 'paid' : 'partially_paid',
            ]);

            $counts['payments']++;
        }
    }

    /**
     * @return array<int, Carbon> every date in $monthDate's month that falls
     *   on $weekday, oldest first
     */
    protected function weekdayDatesInMonth(Carbon $monthDate, int $weekday): array
    {
        $dates = [];
        $cursor = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        while ($cursor->lte($end)) {
            if ($cursor->dayOfWeek === $weekday) {
                $dates[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $dates;
    }
}

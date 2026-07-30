<?php

namespace App\Actions\Billing;

use App\Models\Enrollment;
use App\Models\MonthlyDue;
use App\Support\Activity\TenantActivity;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Generates one MonthlyDue per active enrollment for a given billing month.
 * Idempotent by design: a fresh eligibility check runs before every insert,
 * and the (enrollment_id, billing_year, billing_month) unique index is the
 * hard backstop — running this twice for the same month never duplicates a
 * due, whether triggered manually or replayed by the scheduler after a
 * partial failure.
 */
class GenerateMonthlyDuesAction
{
    /**
     * Dry run — same eligibility query the real generation uses, so what
     * the teacher previews is exactly what "توليد" will create.
     */
    public function preview(int $month, int $year): Collection
    {
        return $this->eligibleEnrollments($month, $year)
            ->whereDoesntHave('monthlyDues', fn ($q) => $q->where('billing_month', $month)->where('billing_year', $year))
            ->with('student', 'group')
            ->get();
    }

    /**
     * @return array{created: int, skipped: int}
     */
    public function execute(int $month, int $year, bool $generatedAutomatically, ?int $generatedBy): array
    {
        $enrollments = $this->eligibleEnrollments($month, $year)->get();
        $created = 0;

        foreach ($enrollments as $enrollment) {
            $exists = MonthlyDue::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('billing_month', $month)
                ->where('billing_year', $year)
                ->exists();

            if ($exists) {
                continue;
            }

            $base = (float) ($enrollment->custom_monthly_price ?? $enrollment->default_monthly_price);
            $final = (float) $enrollment->final_monthly_price;
            $discount = max(0, round($base - $final, 2));

            MonthlyDue::query()->forceCreate([
                'student_id' => $enrollment->student_id,
                'enrollment_id' => $enrollment->id,
                'group_id' => $enrollment->group_id,
                'academic_year_id' => $enrollment->academic_year_id,
                'billing_month' => $month,
                'billing_year' => $year,
                'due_date' => $this->dueDate($month, $year, $enrollment->payment_due_day),
                'base_amount' => $base,
                'discount_amount' => $discount,
                'final_amount' => $final,
                'paid_amount' => 0,
                'remaining_amount' => $final,
                'status' => 'unpaid',
                'generated_automatically' => $generatedAutomatically,
                'generated_by' => $generatedBy,
            ]);

            $created++;
        }

        if ($created > 0) {
            TenantActivity::log('توليد استحقاقات شهرية', null, [
                'month' => $month,
                'year' => $year,
                'count' => $created,
                'automatic' => $generatedAutomatically,
            ]);
        }

        return ['created' => $created, 'skipped' => $enrollments->count() - $created];
    }

    /**
     * Active enrollments that overlapped the billing month at all — joined
     * on/before the month ended, and (still enrolled OR left on/after the
     * month started). A student who joined mid-month or left mid-month
     * still owes for that month; one who joined next month, or left before
     * this one started, does not.
     */
    protected function eligibleEnrollments(int $month, int $year): Builder
    {
        $periodStart = CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();

        return Enrollment::query()
            ->where('status', 'active')
            ->where('joined_at', '<=', $periodEnd)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('left_at')->orWhere('left_at', '>=', $periodStart);
            });
    }

    protected function dueDate(int $month, int $year, ?int $paymentDueDay): \Carbon\Carbon
    {
        $day = $paymentDueDay ?? 1;
        $daysInMonth = CarbonImmutable::create($year, $month, 1)->daysInMonth;

        return \Carbon\Carbon::create($year, $month, min($day, $daysInMonth));
    }
}

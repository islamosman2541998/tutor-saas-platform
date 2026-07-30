<?php

namespace App\Livewire\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TenantDashboardIndex extends Component
{
    /**
     * Every number here is one aggregate query — no per-row PHP loops
     * touching the database, so none of this grows into N+1 as data grows.
     */
    public function render()
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $studentsCount = Student::query()->where('status', 'active')->count();
        $groupsCount = Group::query()->where('status', 'active')->count();

        $sessionsThisMonth = ClassSession::query()
            ->whereBetween('scheduled_date', [$monthStart, $monthEnd])
            ->count();

        $attendanceThisMonth = AttendanceRecord::query()
            ->whereHas('classSession', fn ($q) => $q->whereBetween('scheduled_date', [$monthStart, $monthEnd]))
            ->selectRaw("count(*) as total, sum(status = 'present') as present_count")
            ->first();

        $attendanceRate = $attendanceThisMonth && $attendanceThisMonth->total > 0
            ? round(100 * $attendanceThisMonth->present_count / $attendanceThisMonth->total)
            : null;

        $revenueThisMonth = (float) Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$monthStart, $monthEnd.' 23:59:59'])
            ->sum('amount');

        $overdue = MonthlyDue::query()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->selectRaw('count(*) as count, sum(remaining_amount) as total')
            ->first();

        return view('livewire.dashboard.tenant-dashboard-index', [
            'studentsCount' => $studentsCount,
            'groupsCount' => $groupsCount,
            'sessionsThisMonth' => $sessionsThisMonth,
            'attendanceRate' => $attendanceRate,
            'revenueThisMonth' => $revenueThisMonth,
            'overdueCount' => (int) ($overdue->count ?? 0),
            'overdueTotal' => (float) ($overdue->total ?? 0),
            'revenueChart' => $this->lineChartOptions(
                'الإيرادات',
                $this->monthlySeries(
                    Payment::query()->where('status', 'completed')->where('paid_at', '>=', now()->startOfMonth()->subMonths(5)),
                    'paid_at',
                    'sum(amount)',
                ),
                '#4f46e5',
            ),
            'enrollmentsChart' => $this->lineChartOptions(
                'اشتراكات جديدة',
                $this->monthlySeries(
                    Enrollment::query()->where('joined_at', '>=', now()->startOfMonth()->subMonths(5)),
                    'joined_at',
                    'count(*)',
                ),
                '#10b981',
            ),
            'attendanceChart' => $this->lineChartOptions(
                'نسبة الحضور %',
                $this->attendanceMonthlySeries(),
                '#f59e0b',
            ),
            'paymentMethodChart' => $this->paymentMethodChartOptions($monthStart, $monthEnd),
        ]);
    }

    /**
     * One grouped query for the whole 6-month window, then a cheap PHP pass
     * to fill in months with zero activity — never one query per month.
     *
     * @return array<string, float> "شهر سنة" => value, oldest to newest
     */
    protected function monthlySeries($query, string $dateColumn, string $aggregateExpr): array
    {
        $rows = $query
            ->selectRaw("date_format({$dateColumn}, '%Y-%m') as ym, {$aggregateExpr} as value")
            ->groupBy('ym')
            ->pluck('value', 'ym');

        return $this->fillMonths($rows);
    }

    protected function attendanceMonthlySeries(): array
    {
        $rows = AttendanceRecord::query()
            ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
            ->where('class_sessions.scheduled_date', '>=', now()->startOfMonth()->subMonths(5)->toDateString())
            ->selectRaw("date_format(class_sessions.scheduled_date, '%Y-%m') as ym, count(*) as total, sum(attendance_records.status = 'present') as present_count")
            ->groupBy('ym')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->ym => $row->total > 0 ? round(100 * $row->present_count / $row->total) : 0]);

        return $this->fillMonths($rows);
    }

    /**
     * @param  Collection|array  $rows  "Y-m" => value
     * @return array<string, float> Arabic-labeled month => value, always 6 entries
     */
    protected function fillMonths($rows): array
    {
        $rows = collect($rows);
        $result = [];

        // Anchored at the 1st of the current month *before* subtracting —
        // now()->subMonths($i) directly would overflow into the wrong month
        // whenever today's day-of-month doesn't exist $i months back (e.g.
        // July 29 minus 5 months has no Feb 29, so Carbon rolls over into
        // March), silently colliding two months into one bucket.
        $anchor = now()->startOfMonth();

        for ($i = 5; $i >= 0; $i--) {
            $date = $anchor->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $label = MonthlyDue::monthLabel((int) $date->format('n'));
            $result[$label] = (float) ($rows[$key] ?? 0);
        }

        return $result;
    }

    protected function lineChartOptions(string $seriesName, array $labeledValues, string $color): array
    {
        return [
            'chart' => ['type' => 'area', 'height' => 260, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
            'series' => [['name' => $seriesName, 'data' => array_values($labeledValues)]],
            'xaxis' => ['categories' => array_keys($labeledValues)],
            'colors' => [$color],
            'dataLabels' => ['enabled' => false],
            'stroke' => ['curve' => 'smooth', 'width' => 2],
            'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.35, 'opacityTo' => 0.02]],
            'grid' => ['borderColor' => '#e2e8f0', 'strokeDashArray' => 4],
            'tooltip' => ['theme' => 'light'],
        ];
    }

    protected function paymentMethodChartOptions(string $monthStart, string $monthEnd): array
    {
        $labels = ['cash' => 'نقدًا', 'bank_transfer' => 'تحويل بنكي', 'wallet' => 'محفظة إلكترونية', 'card' => 'بطاقة', 'other' => 'أخرى'];

        $rows = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$monthStart, $monthEnd.' 23:59:59'])
            ->selectRaw('payment_method, sum(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $series = $rows->values()->map(fn ($v) => (float) $v)->all();
        $chartLabels = $rows->keys()->map(fn ($k) => $labels[$k] ?? $k)->all();

        return [
            'chart' => ['type' => 'donut', 'height' => 260, 'fontFamily' => 'inherit'],
            'series' => $series,
            'labels' => $chartLabels,
            'colors' => ['#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#64748b'],
            'legend' => ['position' => 'bottom'],
            'dataLabels' => ['enabled' => count($series) > 0],
        ];
    }
}

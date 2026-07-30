<?php

namespace App\QueryObjects\Reports;

use App\Models\MonthlyDue;
use Illuminate\Database\Eloquent\Builder;

class MonthlyDuesReportQuery
{
    /**
     * @param  array{status?: string, group_id?: int, month?: int, year?: int}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return MonthlyDue::query()
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['group_id'] ?? null, fn (Builder $q, $groupId) => $q->where('group_id', $groupId))
            ->when($filters['month'] ?? null, fn (Builder $q, $month) => $q->where('billing_month', $month))
            ->when($filters['year'] ?? null, fn (Builder $q, $year) => $q->where('billing_year', $year))
            ->orderByDesc('due_date');
    }
}

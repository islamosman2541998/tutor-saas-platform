<?php

namespace App\QueryObjects\Reports;

use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Builder;

class ClassSessionsReportQuery
{
    /**
     * @param  array{status?: string, group_id?: int, from_date?: string, to_date?: string}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return ClassSession::query()
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['group_id'] ?? null, fn (Builder $q, $groupId) => $q->where('group_id', $groupId))
            ->when($filters['from_date'] ?? null, fn (Builder $q, $date) => $q->where('scheduled_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn (Builder $q, $date) => $q->where('scheduled_date', '<=', $date))
            ->orderByDesc('scheduled_date');
    }
}

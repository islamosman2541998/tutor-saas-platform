<?php

namespace App\QueryObjects\Reports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

/**
 * The single source of truth for "which students match these filters" — used
 * both for the on-screen row count (to decide sync vs queued export) and the
 * Excel export itself, so the two can never silently drift apart.
 */
class StudentsReportQuery
{
    /**
     * @param  array{status?: string, search?: string, joined_from?: string, joined_to?: string}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return Student::query()
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['joined_from'] ?? null, fn (Builder $q, $date) => $q->where('joined_at', '>=', $date))
            ->when($filters['joined_to'] ?? null, fn (Builder $q, $date) => $q->where('joined_at', '<=', $date))
            ->orderBy('name');
    }
}

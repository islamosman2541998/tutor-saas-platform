<?php

namespace App\QueryObjects\Reports;

use App\Models\RegistrationRequest;
use Illuminate\Database\Eloquent\Builder;

class RegistrationRequestsReportQuery
{
    /**
     * @param  array{status?: string, group_id?: int, search?: string}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return RegistrationRequest::query()
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['group_id'] ?? null, fn (Builder $q, $groupId) => $q->where('group_id', $groupId))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('student_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at');
    }
}

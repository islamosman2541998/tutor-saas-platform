<?php

namespace App\QueryObjects\Reports;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentsReportQuery
{
    /**
     * @param  array{status?: string, method?: string, from_date?: string, to_date?: string, search?: string}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return Payment::query()
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['method'] ?? null, fn (Builder $q, $method) => $q->where('payment_method', $method))
            ->when($filters['from_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '<=', $date))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('student', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")->orWhere('student_code', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('paid_at');
    }
}

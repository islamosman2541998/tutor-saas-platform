<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsPhoneLikeStringsAsText;
use App\Models\MonthlyDue;
use App\QueryObjects\Reports\MonthlyDuesReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonthlyDuesExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable, ExportsPhoneLikeStringsAsText;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return MonthlyDuesReportQuery::build($this->filters)->with(['student', 'group']);
    }

    public function headings(): array
    {
        return ['الطالب', 'كود الطالب', 'المجموعة', 'الشهر', 'السنة', 'تاريخ الاستحقاق', 'المبلغ النهائي', 'المدفوع', 'المتبقي', 'الحالة'];
    }

    public function map($due): array
    {
        /** @var MonthlyDue $due */
        return [
            $due->student->name,
            $due->student->student_code,
            $due->group->name,
            MonthlyDue::monthLabel($due->billing_month),
            $due->billing_year,
            $due->due_date->format('Y-m-d'),
            (float) $due->final_amount,
            (float) $due->paid_amount,
            (float) $due->remaining_amount,
            $due->statusLabel(),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

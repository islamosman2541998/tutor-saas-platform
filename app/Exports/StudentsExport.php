<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsPhoneLikeStringsAsText;
use App\Models\Student;
use App\QueryObjects\Reports\StudentsReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * WithChunkReading keeps memory flat regardless of how many students match —
 * the same class is used for an immediate download (small result) and a
 * queued background export (large result); only the caller decides which.
 */
class StudentsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable, ExportsPhoneLikeStringsAsText;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return StudentsReportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return ['كود الطالب', 'الاسم', 'الهاتف', 'ولي الأمر', 'هاتف ولي الأمر', 'الحالة', 'تاريخ الانضمام'];
    }

    public function map($student): array
    {
        /** @var Student $student */
        return [
            $student->student_code,
            $student->name,
            $student->phone,
            $student->guardian_name,
            $student->guardian_phone,
            $student->statusLabel(),
            optional($student->joined_at)->format('Y-m-d'),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

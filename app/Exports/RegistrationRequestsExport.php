<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsPhoneLikeStringsAsText;
use App\Models\RegistrationRequest;
use App\QueryObjects\Reports\RegistrationRequestsReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrationRequestsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable, ExportsPhoneLikeStringsAsText;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return RegistrationRequestsReportQuery::build($this->filters)->with('group');
    }

    public function headings(): array
    {
        return ['اسم الطالب', 'الهاتف', 'هاتف ولي الأمر', 'المجموعة', 'الحالة', 'تاريخ الطلب'];
    }

    public function map($request): array
    {
        /** @var RegistrationRequest $request */
        return [
            $request->student_name,
            $request->phone,
            $request->guardian_phone,
            $request->group->name,
            $request->statusLabel(),
            $request->created_at->format('Y-m-d H:i'),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

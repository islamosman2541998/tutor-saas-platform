<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsPhoneLikeStringsAsText;
use App\Models\ClassSession;
use App\QueryObjects\Reports\ClassSessionsReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassSessionsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable, ExportsPhoneLikeStringsAsText;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return ClassSessionsReportQuery::build($this->filters)->with('group');
    }

    public function headings(): array
    {
        return ['المجموعة', 'تاريخ الحصة', 'الوقت المتوقع', 'بدأت الساعة', 'انتهت الساعة', 'الحالة', 'الموضوع'];
    }

    public function map($session): array
    {
        /** @var ClassSession $session */
        return [
            $session->group->name,
            $session->scheduled_date->format('Y-m-d'),
            $session->expected_start_time.' - '.$session->expected_end_time,
            optional($session->actual_started_at)->format('Y-m-d H:i'),
            optional($session->actual_closed_at)->format('Y-m-d H:i'),
            $session->statusLabel(),
            $session->lesson_topic,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

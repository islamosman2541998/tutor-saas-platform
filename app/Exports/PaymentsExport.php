<?php

namespace App\Exports;

use App\Models\Payment;
use App\QueryObjects\Reports\PaymentsReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return PaymentsReportQuery::build($this->filters)->with('student');
    }

    public function headings(): array
    {
        return ['رقم الإيصال', 'الطالب', 'كود الطالب', 'المبلغ', 'طريقة الدفع', 'تاريخ الدفع', 'الحالة'];
    }

    public function map($payment): array
    {
        /** @var Payment $payment */
        return [
            $payment->receipt_number,
            $payment->student->name,
            $payment->student->student_code,
            (float) $payment->amount,
            $payment->methodLabel(),
            $payment->paid_at->format('Y-m-d H:i'),
            $payment->statusLabel(),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

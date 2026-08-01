<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsPhoneLikeStringsAsText;
use App\Models\User;
use App\QueryObjects\Reports\UsersReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable, ExportsPhoneLikeStringsAsText;

    public function __construct(protected array $filters = []) {}

    public function query(): Builder
    {
        return UsersReportQuery::build($this->filters)->with('roles');
    }

    public function headings(): array
    {
        return ['الاسم', 'البريد الإلكتروني', 'الهاتف', 'الأدوار', 'الحالة', 'آخر تسجيل دخول'];
    }

    public function map($user): array
    {
        /** @var User $user */
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->getRoleNames()->implode('، '),
            $user->is_active ? 'مفعل' : 'موقوف',
            optional($user->last_login_at)->format('Y-m-d H:i'),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

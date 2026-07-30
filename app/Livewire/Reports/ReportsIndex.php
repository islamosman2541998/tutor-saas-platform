<?php

namespace App\Livewire\Reports;

use App\Exports\MonthlyDuesExport;
use App\Exports\PaymentsExport;
use App\Exports\StudentsExport;
use App\Jobs\GenerateReportExportJob;
use App\Livewire\Concerns\Notifies;
use App\Models\Group;
use App\QueryObjects\Reports\MonthlyDuesReportQuery;
use App\QueryObjects\Reports\PaymentsReportQuery;
use App\QueryObjects\Reports\StudentsReportQuery;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ReportsIndex extends Component
{
    use Notifies;

    public string $activeTab = 'students';

    // Students filters
    public string $studentsStatus = '';

    public string $studentsSearch = '';

    // Payments filters
    public string $paymentsStatus = '';

    public string $paymentsMethod = '';

    public string $paymentsFrom = '';

    public string $paymentsTo = '';

    // Monthly dues filters
    public string $duesStatus = '';

    public string $duesGroupId = '';

    public function mount(): void
    {
        $this->authorize('reports.view');
    }

    public function exportStudents(): mixed
    {
        return $this->export('students', $this->studentsFilters(), 'الطلاب', new StudentsExport($this->studentsFilters()));
    }

    public function exportPayments(): mixed
    {
        return $this->export('payments', $this->paymentsFilters(), 'الدفعات', new PaymentsExport($this->paymentsFilters()));
    }

    public function exportMonthlyDues(): mixed
    {
        return $this->export('monthly_dues', $this->duesFilters(), 'المستحقات الشهرية', new MonthlyDuesExport($this->duesFilters()));
    }

    /**
     * Small result → stream the download immediately (Livewire natively
     * turns a returned BinaryFileResponse into a browser download).
     * Large result → queue GenerateReportExportJob and email a signed link
     * instead of holding the request open building a huge file.
     */
    protected function export(string $type, array $filters, string $label, mixed $exportInstance): mixed
    {
        $this->authorize('reports.export');

        $query = match ($type) {
            'students' => StudentsReportQuery::build($filters),
            'payments' => PaymentsReportQuery::build($filters),
            'monthly_dues' => MonthlyDuesReportQuery::build($filters),
        };

        $count = $query->count();

        if ($count === 0) {
            $this->toast('لا توجد بيانات مطابقة لهذه الفلاتر.', 'error');

            return null;
        }

        if ($count <= config('reports.queue_threshold')) {
            // Deliberately not Str::slug($label) — it transliterates Arabic
            // into an unreadable mess ("الطلاب" -> "altlab") for what's
            // otherwise an Arabic-first product; browsers handle UTF-8
            // filenames in Content-Disposition fine.
            return $exportInstance->download("{$label}-".now()->format('Y-m-d').'.xlsx');
        }

        GenerateReportExportJob::dispatch(
            app(TenantContext::class)->id(),
            $type,
            $filters,
            Str::random(40).'.xlsx',
            auth()->user()->email,
            $label,
        );

        $this->toast("التقرير يحتوي على {$count} سجل — جاري إعداده في الخلفية، وستصلك رسالة بريد إلكتروني تحتوي رابط التحميل عند الانتهاء.");

        return null;
    }

    protected function studentsFilters(): array
    {
        return array_filter([
            'status' => $this->studentsStatus,
            'search' => $this->studentsSearch,
        ]);
    }

    protected function paymentsFilters(): array
    {
        return array_filter([
            'status' => $this->paymentsStatus,
            'method' => $this->paymentsMethod,
            'from_date' => $this->paymentsFrom,
            'to_date' => $this->paymentsTo,
        ]);
    }

    protected function duesFilters(): array
    {
        return array_filter([
            'status' => $this->duesStatus,
            'group_id' => $this->duesGroupId,
        ]);
    }

    public function render()
    {
        return view('livewire.reports.reports-index', [
            'groups' => Group::query()->orderBy('name')->get(),
            'studentsCount' => StudentsReportQuery::build($this->studentsFilters())->count(),
            'paymentsCount' => PaymentsReportQuery::build($this->paymentsFilters())->count(),
            'duesCount' => MonthlyDuesReportQuery::build($this->duesFilters())->count(),
        ]);
    }
}

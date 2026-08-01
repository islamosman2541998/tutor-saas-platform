<?php

namespace App\Livewire\Reports;

use App\Exports\ClassSessionsExport;
use App\Exports\MonthlyDuesExport;
use App\Exports\PaymentsExport;
use App\Exports\RegistrationRequestsExport;
use App\Exports\StudentsExport;
use App\Exports\UsersExport;
use App\Jobs\GenerateReportExportJob;
use App\Livewire\Concerns\Notifies;
use App\Models\Group;
use App\QueryObjects\Reports\ClassSessionsReportQuery;
use App\QueryObjects\Reports\MonthlyDuesReportQuery;
use App\QueryObjects\Reports\PaymentsReportQuery;
use App\QueryObjects\Reports\RegistrationRequestsReportQuery;
use App\QueryObjects\Reports\StudentsReportQuery;
use App\QueryObjects\Reports\UsersReportQuery;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ReportsIndex extends Component
{
    use Notifies, WithPagination;

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

    // Users filters
    public string $usersStatus = '';

    public string $usersSearch = '';

    // Registration requests filters
    public string $registrationsStatus = '';

    public string $registrationsGroupId = '';

    public string $registrationsSearch = '';

    // Class sessions filters
    public string $sessionsStatus = '';

    public string $sessionsGroupId = '';

    public string $sessionsFrom = '';

    public string $sessionsTo = '';

    public function mount(): void
    {
        $this->authorize('reports.view');
    }

    /**
     * Every public property here is either the active tab or a filter —
     * switching either one invalidates the current preview page, so reset
     * to page 1 rather than showing a now-out-of-range page.
     */
    public function updated(): void
    {
        $this->resetPage();
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

    public function exportUsers(): mixed
    {
        return $this->export('users', $this->usersFilters(), 'المستخدمين', new UsersExport($this->usersFilters()));
    }

    public function exportRegistrationRequests(): mixed
    {
        return $this->export('registration_requests', $this->registrationsFilters(), 'طلبات التسجيل', new RegistrationRequestsExport($this->registrationsFilters()));
    }

    public function exportClassSessions(): mixed
    {
        return $this->export('class_sessions', $this->sessionsFilters(), 'الحصص الدراسية', new ClassSessionsExport($this->sessionsFilters()));
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
            'users' => UsersReportQuery::build($filters),
            'registration_requests' => RegistrationRequestsReportQuery::build($filters),
            'class_sessions' => ClassSessionsReportQuery::build($filters),
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

    protected function usersFilters(): array
    {
        return array_filter([
            'status' => $this->usersStatus,
            'search' => $this->usersSearch,
        ]);
    }

    protected function registrationsFilters(): array
    {
        return array_filter([
            'status' => $this->registrationsStatus,
            'group_id' => $this->registrationsGroupId,
            'search' => $this->registrationsSearch,
        ]);
    }

    protected function sessionsFilters(): array
    {
        return array_filter([
            'status' => $this->sessionsStatus,
            'group_id' => $this->sessionsGroupId,
            'from_date' => $this->sessionsFrom,
            'to_date' => $this->sessionsTo,
        ]);
    }

    /**
     * Only the visible tab's rows are fetched (with its relations eager
     * loaded) — the other five tabs still get a cheap count() below so
     * their "matching records" figure stays live, but not a full row fetch.
     */
    protected function previewRows(): mixed
    {
        return match ($this->activeTab) {
            'students' => StudentsReportQuery::build($this->studentsFilters())->paginate(15),
            'payments' => PaymentsReportQuery::build($this->paymentsFilters())->with('student')->paginate(15),
            'monthly_dues' => MonthlyDuesReportQuery::build($this->duesFilters())->with(['student', 'group'])->paginate(15),
            'users' => UsersReportQuery::build($this->usersFilters())->with('roles')->paginate(15),
            'registration_requests' => RegistrationRequestsReportQuery::build($this->registrationsFilters())->with('group')->paginate(15),
            'class_sessions' => ClassSessionsReportQuery::build($this->sessionsFilters())->with('group')->paginate(15),
        };
    }

    public function render()
    {
        return view('livewire.reports.reports-index', [
            'groups' => Group::query()->orderBy('name')->get(),
            'previewRows' => $this->previewRows(),
            'studentsCount' => StudentsReportQuery::build($this->studentsFilters())->count(),
            'paymentsCount' => PaymentsReportQuery::build($this->paymentsFilters())->count(),
            'duesCount' => MonthlyDuesReportQuery::build($this->duesFilters())->count(),
            'usersCount' => UsersReportQuery::build($this->usersFilters())->count(),
            'registrationsCount' => RegistrationRequestsReportQuery::build($this->registrationsFilters())->count(),
            'sessionsCount' => ClassSessionsReportQuery::build($this->sessionsFilters())->count(),
        ]);
    }
}

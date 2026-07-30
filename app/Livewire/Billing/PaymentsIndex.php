<?php

namespace App\Livewire\Billing;

use App\Actions\Billing\CancelPaymentAction;
use App\Actions\Billing\RecordPaymentAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PaymentsIndex extends Component
{
    use Notifies, WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $methodFilter = '';

    public string $fromDate = '';

    public string $toDate = '';

    public bool $showRecordForm = false;

    public string $studentSearch = '';

    public ?int $selectedStudentId = null;

    /** @var array<int, bool> keyed by monthly_due_id */
    public array $selectedDues = [];

    /** @var array<int, string> keyed by monthly_due_id */
    public array $dueAmounts = [];

    public string $payment_method = 'cash';

    public string $paid_at = '';

    public string $reference_number = '';

    public string $notes = '';

    public $attachment = null;

    public bool $showCancelForm = false;

    public ?int $cancelingPaymentId = null;

    public string $cancelReason = '';

    public function mount(): void
    {
        if (request()->query('student') && $this->authorizeSilently('payments.record')) {
            $this->openRecordForm();
            $this->pickStudent((int) request()->query('student'));
        }
    }

    protected function authorizeSilently(string $ability): bool
    {
        return auth()->user()?->can($ability) ?? false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFromDate(): void
    {
        $this->resetPage();
    }

    public function updatingToDate(): void
    {
        $this->resetPage();
    }

    public function updatedStudentSearch(): void
    {
        $this->selectedStudentId = null;
        $this->selectedDues = [];
        $this->dueAmounts = [];
    }

    public function pickStudent(int $studentId): void
    {
        $student = Student::query()->findOrFail($studentId);
        $this->selectedStudentId = $studentId;
        $this->studentSearch = $student->name;

        $this->selectedDues = [];
        $this->dueAmounts = [];

        foreach ($this->outstandingDuesFor($studentId) as $due) {
            $this->dueAmounts[$due->id] = (string) $due->remaining_amount;
        }
    }

    public function openRecordForm(): void
    {
        $this->authorize('payments.record');

        $this->reset(['studentSearch', 'selectedStudentId', 'selectedDues', 'dueAmounts', 'reference_number', 'notes', 'attachment']);
        $this->payment_method = 'cash';
        $this->paid_at = now()->format('Y-m-d\TH:i');
        $this->showRecordForm = true;
    }

    public function recordPayment(RecordPaymentAction $action): void
    {
        $this->authorize('payments.record');

        $this->validate([
            'selectedStudentId' => ['required'],
            'payment_method' => ['required', 'in:cash,bank_transfer,wallet,card,other'],
            'paid_at' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ], [], [
            'selectedStudentId' => 'الطالب',
            'paid_at' => 'تاريخ الدفع',
        ]);

        $items = [];
        foreach ($this->selectedDues as $dueId => $checked) {
            if (! $checked) {
                continue;
            }

            $amount = (float) ($this->dueAmounts[$dueId] ?? 0);
            $items[] = ['monthly_due_id' => (int) $dueId, 'amount' => $amount];
        }

        if (empty($items)) {
            $this->toast('اختر استحقاقًا واحدًا على الأقل لتسجيل دفعة.', 'error');

            return;
        }

        $student = Student::query()->findOrFail($this->selectedStudentId);

        try {
            $payments = $action->execute($student, $items, [
                'payment_method' => $this->payment_method,
                'paid_at' => $this->paid_at,
                'reference_number' => $this->reference_number ?: null,
                'notes' => $this->notes ?: null,
            ], $this->attachment ?: null);
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->toast(
            $payments->count() === 1
                ? 'تم تسجيل الدفعة بنجاح.'
                : "تم تسجيل {$payments->count()} دفعات بنجاح."
        );

        $this->showRecordForm = false;
    }

    public function openCancelForm(int $paymentId): void
    {
        $this->authorize('payments.cancel');

        $this->cancelingPaymentId = $paymentId;
        $this->cancelReason = '';
        $this->showCancelForm = true;
    }

    public function saveCancel(CancelPaymentAction $action): void
    {
        $this->authorize('payments.cancel');

        $data = $this->validate([
            'cancelReason' => ['required', 'string', 'max:500'],
        ], [], ['cancelReason' => 'سبب الإلغاء']);

        try {
            $action->execute(Payment::query()->findOrFail($this->cancelingPaymentId), $data['cancelReason']);
            $this->toast('تم إلغاء الدفعة.');
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->showCancelForm = false;
    }

    protected function outstandingDuesFor(int $studentId)
    {
        return MonthlyDue::query()
            ->where('student_id', $studentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date')
            ->get();
    }

    protected function filteredQuery()
    {
        return Payment::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('receipt_number', 'like', "%{$this->search}%")
                        ->orWhereHas('student', fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('student_code', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->methodFilter, fn ($q) => $q->where('payment_method', $this->methodFilter))
            ->when($this->fromDate, fn ($q) => $q->whereDate('paid_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('paid_at', '<=', $this->toDate));
    }

    public function render()
    {
        $payments = $this->filteredQuery()
            ->with(['student', 'monthlyDue'])
            ->orderByDesc('paid_at')
            ->paginate(15);

        $totals = $this->filteredQuery()->where('status', 'completed')->selectRaw('sum(amount) as total')->first();

        return view('livewire.billing.payments-index', [
            'payments' => $payments,
            'outstandingDues' => $this->selectedStudentId ? $this->outstandingDuesFor($this->selectedStudentId) : collect(),
            'searchResults' => $this->studentSearch && ! $this->selectedStudentId
                ? Student::query()
                    ->where(function ($q) {
                        $q->where('name', 'like', "%{$this->studentSearch}%")
                            ->orWhere('student_code', 'like', "%{$this->studentSearch}%");
                    })
                    ->limit(8)
                    ->get()
                : collect(),
            'totalCompleted' => (float) ($totals->total ?? 0),
        ]);
    }
}

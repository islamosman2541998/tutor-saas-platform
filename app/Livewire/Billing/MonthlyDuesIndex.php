<?php

namespace App\Livewire\Billing;

use App\Actions\Billing\GenerateMonthlyDuesAction;
use App\Actions\Billing\SetMonthlyDueStatusAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\Group;
use App\Models\MonthlyDue;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MonthlyDuesIndex extends Component
{
    use Notifies, WithPagination;

    public string $monthFilter = '';

    public string $yearFilter = '';

    public string $statusFilter = '';

    public string $groupFilter = '';

    public bool $showGenerateForm = false;

    public string $generateMonth = '';

    public string $generateYear = '';

    public ?array $preview = null;

    public bool $showWaiveForm = false;

    public ?int $waivingDueId = null;

    public string $waiveStatus = 'waived';

    public string $waiveReason = '';

    public function mount(): void
    {
        $this->monthFilter = (string) now()->month;
        $this->yearFilter = (string) now()->year;
    }

    public function updatingMonthFilter(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingGroupFilter(): void
    {
        $this->resetPage();
    }

    public function openGenerateForm(): void
    {
        $this->authorize('payments.record');

        $this->generateMonth = (string) now()->month;
        $this->generateYear = (string) now()->year;
        $this->preview = null;
        $this->showGenerateForm = true;
    }

    public function runPreview(GenerateMonthlyDuesAction $action): void
    {
        $this->authorize('payments.record');

        $eligible = $action->preview((int) $this->generateMonth, (int) $this->generateYear);

        $this->preview = [
            'count' => $eligible->count(),
            'total' => $eligible->sum('final_monthly_price'),
        ];
    }

    public function confirmGenerate(GenerateMonthlyDuesAction $action): void
    {
        $this->authorize('payments.record');

        $result = $action->execute(
            (int) $this->generateMonth,
            (int) $this->generateYear,
            generatedAutomatically: false,
            generatedBy: auth()->id(),
        );

        $this->toast(
            $result['created'] > 0
                ? "تم إنشاء {$result['created']} استحقاق جديد."
                : 'كل الاستحقاقات لهذا الشهر موجودة بالفعل.'
        );

        $this->showGenerateForm = false;
        $this->preview = null;
    }

    public function openWaiveForm(int $dueId): void
    {
        $this->authorize('payments.cancel');

        $this->waivingDueId = $dueId;
        $this->waiveStatus = 'waived';
        $this->waiveReason = '';
        $this->showWaiveForm = true;
    }

    public function saveWaive(SetMonthlyDueStatusAction $action): void
    {
        $this->authorize('payments.cancel');

        $data = $this->validate([
            'waiveStatus' => ['required', 'in:waived,cancelled'],
            'waiveReason' => ['required', 'string', 'max:500'],
        ], [], ['waiveReason' => 'السبب']);

        try {
            $action->execute(MonthlyDue::query()->findOrFail($this->waivingDueId), $data['waiveStatus'], $data['waiveReason']);
            $this->toast('تم تحديث حالة الاستحقاق.');
        } catch (CannotPerformActionException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }

        $this->showWaiveForm = false;
    }

    protected function filteredQuery()
    {
        return MonthlyDue::query()
            ->when($this->monthFilter, fn ($q) => $q->where('billing_month', $this->monthFilter))
            ->when($this->yearFilter, fn ($q) => $q->where('billing_year', $this->yearFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->groupFilter, fn ($q) => $q->where('group_id', $this->groupFilter));
    }

    public function render()
    {
        $dues = $this->filteredQuery()
            ->with(['student', 'group'])
            ->orderByDesc('due_date')
            ->paginate(15);

        $totals = $this->filteredQuery()->selectRaw('
            sum(final_amount) as final,
            sum(paid_amount) as paid,
            sum(remaining_amount) as remaining
        ')->first();

        return view('livewire.billing.monthly-dues-index', [
            'dues' => $dues,
            'groups' => Group::query()->orderBy('name')->get(),
            'totals' => $totals,
        ]);
    }
}

<?php

namespace App\Livewire\Billing;

use App\Actions\Billing\SetMonthlyDueStatusAction;
use App\Exceptions\CannotPerformActionException;
use App\Livewire\Concerns\Notifies;
use App\Models\Group;
use App\Models\MonthlyDue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class OverdueDuesIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $groupFilter = '';

    public string $minDaysOverdue = '';

    public bool $showWaiveForm = false;

    public ?int $waivingDueId = null;

    public string $waiveStatus = 'waived';

    public string $waiveReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMinDaysOverdue(): void
    {
        $this->resetPage();
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

    /**
     * Overdue = unpaid or partially-paid with a due_date already in the
     * past. "متأخر X يوم فأكثر" is expressed as due_date <= today - X days
     * rather than a HAVING on a computed alias, so this same WHERE-only
     * query can back both the paginated list and the summary aggregate
     * without the two SELECT clauses colliding.
     */
    protected function filteredQuery(): Builder
    {
        $today = Carbon::today();

        return MonthlyDue::query()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', $today->toDateString())
            ->when($this->search, function ($q) {
                $q->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('student_code', 'like', "%{$this->search}%"));
            })
            ->when($this->groupFilter, fn ($q) => $q->where('group_id', $this->groupFilter))
            ->when($this->minDaysOverdue !== '', fn ($q) => $q->where('due_date', '<=', $today->copy()->subDays((int) $this->minDaysOverdue)->toDateString()));
    }

    public function render()
    {
        $today = Carbon::today()->toDateString();

        $dues = $this->filteredQuery()
            ->with(['student', 'group'])
            ->selectRaw('monthly_dues.*, DATEDIFF(?, due_date) as days_overdue', [$today])
            ->orderByDesc('days_overdue')
            ->paginate(15);

        $summary = $this->filteredQuery()->selectRaw('
            count(*) as count,
            sum(remaining_amount) as total_remaining,
            count(distinct student_id) as students_count
        ')->first();

        return view('livewire.billing.overdue-dues-index', [
            'dues' => $dues,
            'groups' => Group::query()->orderBy('name')->get(),
            'summary' => $summary,
        ]);
    }
}

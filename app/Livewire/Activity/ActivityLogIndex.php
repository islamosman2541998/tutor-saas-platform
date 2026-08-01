<?php

namespace App\Livewire\Activity;

use App\Models\Activity;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $causerId = '';

    public string $fromDate = '';

    public string $toDate = '';

    public ?int $viewingId = null;

    public function mount(): void
    {
        $this->authorize('activity.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCauserId(): void
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

    public function viewDetails(int $activityId): void
    {
        $this->viewingId = $activityId;
    }

    public function closeDetails(): void
    {
        $this->viewingId = null;
    }

    /**
     * Activity (like User) carries a tenant_id but has no BelongsToTenant/
     * TenantScope — a Super Admin action (e.g. approving a tenant) is
     * legitimately logged with tenant_id set but a causer who belongs to no
     * tenant at all, which TenantScope's team-based filtering isn't built
     * for. Every query here filters tenant_id explicitly instead.
     */
    public function render()
    {
        $tenantId = app(TenantContext::class)->id();

        $activities = Activity::query()
            ->where('tenant_id', $tenantId)
            ->when($this->search, fn (Builder $q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->causerId, fn (Builder $q) => $q->where('causer_id', $this->causerId)->where('causer_type', User::class))
            ->when($this->fromDate, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($this->toDate, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->with('causer')
            ->latest()
            ->paginate(20);

        return view('livewire.activity.activity-log-index', [
            'activities' => $activities,
            'causers' => User::query()->where('tenant_id', $tenantId)->orderBy('name')->get(),
            // Scoped by tenant_id too — viewingId is only ever set from an
            // id already inside the tenant-filtered list above, but a
            // crafted wire:click could still pass an arbitrary id.
            'viewingActivity' => $this->viewingId
                ? Activity::query()->where('tenant_id', $tenantId)->find($this->viewingId)
                : null,
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Actions\Tenancy\AssignTenantSubscriptionAction;
use App\Actions\Tenancy\SetTenantStatusAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Plan;
use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TenantsIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $managingTenantId = null;

    public string $plan_id = '';

    public string $billing_cycle = 'monthly';

    public string $subscription_status = 'active';

    public string $starts_at = '';

    public string $ends_at = '';

    public string $amount = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function askToggleStatus(int $tenantId, string $newStatus): void
    {
        $tenant = Tenant::query()->findOrFail($tenantId);

        [$title, $text, $confirmButtonText] = match ($newStatus) {
            'active' => [
                'تفعيل حساب المدرس؟',
                "سيتمكن {$tenant->teacher_name} من الدخول لحسابه.",
                'تفعيل',
            ],
            'suspended' => [
                'إيقاف حساب المدرس؟',
                "لن يتمكن {$tenant->teacher_name} من الدخول لحسابه حتى يُفعَّل مجددًا.",
                'إيقاف',
            ],
            'rejected' => [
                'رفض طلب التسجيل؟',
                "سيتم رفض طلب {$tenant->teacher_name} ولن يتمكن من الدخول لحسابه.",
                'رفض',
            ],
            default => ['تحديث حالة الحساب؟', '', 'تأكيد'],
        };

        $this->confirmThen(
            confirmEvent: 'tenant-status-confirmed',
            title: $title,
            text: $text,
            params: ['tenantId' => $tenantId, 'newStatus' => $newStatus],
            confirmButtonText: $confirmButtonText,
        );
    }

    #[\Livewire\Attributes\On('tenant-status-confirmed')]
    public function toggleStatus(int $tenantId, string $newStatus, SetTenantStatusAction $action): void
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $action->execute($tenant, $newStatus);

        $this->toast(match ($newStatus) {
            'active' => 'تم تفعيل الحساب.',
            'suspended' => 'تم إيقاف الحساب.',
            'rejected' => 'تم رفض الطلب.',
            default => 'تم تحديث الحالة.',
        });
    }

    public function openSubscriptionForm(int $tenantId): void
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $current = $tenant->currentSubscription();

        $this->managingTenantId = $tenantId;
        $this->plan_id = (string) ($current->plan_id ?? '');
        $this->billing_cycle = $current->billing_cycle ?? 'monthly';
        $this->subscription_status = $current->status ?? 'active';
        $this->starts_at = $current?->starts_at?->toDateString() ?? now()->toDateString();
        $this->ends_at = $current?->ends_at?->toDateString() ?? '';
        $this->amount = $current ? (string) $current->amount : '';
    }

    public function closeSubscriptionForm(): void
    {
        $this->reset(['managingTenantId', 'plan_id', 'billing_cycle', 'subscription_status', 'starts_at', 'ends_at', 'amount']);
    }

    public function updatedPlanId(): void
    {
        $this->recalculateAmount();
    }

    public function updatedBillingCycle(): void
    {
        $this->recalculateAmount();
    }

    protected function recalculateAmount(): void
    {
        $plan = Plan::query()->find($this->plan_id);

        if (! $plan) {
            return;
        }

        $this->amount = (string) ($this->billing_cycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price);
    }

    public function saveSubscription(AssignTenantSubscriptionAction $action): void
    {
        $data = $this->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'subscription_status' => ['required', 'in:trial,active,expired,cancelled,suspended'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'amount' => ['required', 'numeric', 'min:0'],
        ], [], [
            'plan_id' => 'الباقة',
            'billing_cycle' => 'دورة الفوترة',
            'subscription_status' => 'حالة الاشتراك',
            'starts_at' => 'تاريخ البداية',
            'ends_at' => 'تاريخ النهاية',
            'amount' => 'المبلغ',
        ]);

        $tenant = Tenant::query()->findOrFail($this->managingTenantId);

        $action->execute($tenant, [
            'plan_id' => $data['plan_id'],
            'billing_cycle' => $data['billing_cycle'],
            'status' => $data['subscription_status'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?: null,
            'amount' => $data['amount'],
        ]);

        $this->toast('تم حفظ اشتراك المدرس.');
        $this->closeSubscriptionForm();
    }

    public function render()
    {
        $tenants = Tenant::query()
            ->with(['subscriptions' => fn ($q) => $q->latest('starts_at')->limit(1)->with('plan')])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('teacher_name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.tenants-index', [
            'tenants' => $tenants,
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}

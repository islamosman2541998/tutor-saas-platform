<?php

namespace App\Livewire\Admin;

use App\Actions\Tenancy\RecordSubscriptionPaymentAction;
use App\Livewire\Concerns\Notifies;
use App\Models\Plan;
use App\Models\TenantSubscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SubscriptionsIndex extends Component
{
    use Notifies, WithPagination;

    #[Url]
    public string $search = '';

    public string $statusFilter = '';

    public string $planFilter = '';

    public ?int $recordingSubscriptionId = null;

    public ?int $viewingSubscriptionId = null;

    public string $amount = '';

    public string $payment_method = 'cash';

    public string $paid_at = '';

    public string $reference_number = '';

    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPlanFilter(): void
    {
        $this->resetPage();
    }

    public function openPaymentForm(int $subscriptionId): void
    {
        $this->recordingSubscriptionId = $subscriptionId;
        $this->amount = '';
        $this->payment_method = 'cash';
        $this->paid_at = now()->toDateString();
        $this->reference_number = '';
        $this->notes = '';
    }

    public function closePaymentForm(): void
    {
        $this->reset(['recordingSubscriptionId', 'amount', 'payment_method', 'paid_at', 'reference_number', 'notes']);
    }

    public function savePayment(RecordSubscriptionPaymentAction $action): void
    {
        $data = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank_transfer,wallet,card,other'],
            'paid_at' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'amount' => 'المبلغ',
            'payment_method' => 'طريقة الدفع',
            'paid_at' => 'تاريخ الدفع',
            'reference_number' => 'رقم الإيصال',
            'notes' => 'ملاحظات',
        ]);

        $subscription = TenantSubscription::query()->findOrFail($this->recordingSubscriptionId);
        $action->execute($subscription, $data);

        $this->toast('تم تسجيل الدفعة.');
        $this->closePaymentForm();
    }

    public function viewPayments(int $subscriptionId): void
    {
        $this->viewingSubscriptionId = $subscriptionId;
    }

    public function closePaymentsHistory(): void
    {
        $this->viewingSubscriptionId = null;
    }

    public function render()
    {
        $subscriptions = TenantSubscription::query()
            ->with(['tenant', 'plan', 'payments'])
            ->when($this->search, fn ($q) => $q->whereHas('tenant', function ($q) {
                $q->where('teacher_name', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->planFilter, fn ($q) => $q->where('plan_id', $this->planFilter))
            ->latest('starts_at')
            ->paginate(15);

        return view('livewire.admin.subscriptions-index', [
            'subscriptions' => $subscriptions,
            'plans' => Plan::query()->orderBy('sort_order')->get(),
            'viewingSubscription' => $this->viewingSubscriptionId
                ? TenantSubscription::query()->with(['payments.recordedBy', 'tenant'])->find($this->viewingSubscriptionId)
                : null,
        ]);
    }
}

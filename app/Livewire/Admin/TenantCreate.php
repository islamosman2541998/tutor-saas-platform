<?php

namespace App\Livewire\Admin;

use App\Actions\Tenancy\AssignTenantSubscriptionAction;
use App\Actions\Tenancy\RegisterTenantAction;
use App\Models\Plan;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TenantCreate extends Component
{
    public string $teacher_name = '';

    public string $activity_name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $plan_id = '';

    public string $billing_cycle = 'monthly';

    public string $amount = '';

    public string $starts_at = '';

    public function mount(): void
    {
        $this->starts_at = now()->toDateString();
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

    public function save(RegisterTenantAction $registerTenant, AssignTenantSubscriptionAction $assignSubscription)
    {
        $data = $this->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'activity_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'amount' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
        ], [], [
            'teacher_name' => 'اسم المدرس',
            'activity_name' => 'اسم النشاط أو السنتر',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور',
            'plan_id' => 'الباقة',
            'billing_cycle' => 'دورة الفوترة',
            'amount' => 'المبلغ',
            'starts_at' => 'تاريخ بداية الاشتراك',
        ]);

        DB::transaction(function () use ($data, $registerTenant, $assignSubscription) {
            $tenant = $registerTenant->execute([...$data, 'status' => 'active']);

            $assignSubscription->execute($tenant, [
                'plan_id' => $data['plan_id'],
                'billing_cycle' => $data['billing_cycle'],
                'status' => 'active',
                'starts_at' => $data['starts_at'],
                'amount' => $data['amount'],
            ]);

            TenantActivity::log('تم إنشاء الحساب يدويًا بواسطة السوبر أدمن', $tenant);
        });

        $this->redirectRoute('admin.tenants', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.tenant-create', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}

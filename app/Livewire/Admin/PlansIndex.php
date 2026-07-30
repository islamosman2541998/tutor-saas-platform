<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\Notifies;
use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PlansIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $monthly_price = '';

    public string $yearly_price = '';

    public string $trial_days = '0';

    public string $max_students = '';

    public string $max_groups = '';

    public string $max_users = '';

    public bool $website_enabled = true;

    public bool $custom_domain_enabled = false;

    public bool $excel_export_enabled = true;

    public bool $advanced_reports_enabled = false;

    public bool $is_active = true;

    public function create(): void
    {
        $this->reset([
            'editingId', 'name', 'monthly_price', 'yearly_price', 'trial_days',
            'max_students', 'max_groups', 'max_users',
        ]);
        $this->website_enabled = true;
        $this->custom_domain_enabled = false;
        $this->excel_export_enabled = true;
        $this->advanced_reports_enabled = false;
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->monthly_price = (string) $plan->monthly_price;
        $this->yearly_price = (string) $plan->yearly_price;
        $this->trial_days = (string) $plan->trial_days;
        $this->max_students = (string) $plan->max_students;
        $this->max_groups = (string) $plan->max_groups;
        $this->max_users = (string) $plan->max_users;
        $this->website_enabled = $plan->website_enabled;
        $this->custom_domain_enabled = $plan->custom_domain_enabled;
        $this->excel_export_enabled = $plan->excel_export_enabled;
        $this->advanced_reports_enabled = $plan->advanced_reports_enabled;
        $this->is_active = $plan->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['nullable', 'numeric', 'min:0'],
            'trial_days' => ['required', 'integer', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_groups' => ['nullable', 'integer', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'اسم الباقة',
            'monthly_price' => 'السعر الشهري',
            'yearly_price' => 'السعر السنوي',
            'trial_days' => 'أيام الفترة التجريبية',
            'max_students' => 'الحد الأقصى للطلاب',
            'max_groups' => 'الحد الأقصى للمجموعات',
            'max_users' => 'الحد الأقصى للمستخدمين',
        ]);

        $data['yearly_price'] = $data['yearly_price'] ?: null;
        $data['max_students'] = $data['max_students'] ?: null;
        $data['max_groups'] = $data['max_groups'] ?: null;
        $data['max_users'] = $data['max_users'] ?: null;
        $data['website_enabled'] = $this->website_enabled;
        $data['custom_domain_enabled'] = $this->custom_domain_enabled;
        $data['excel_export_enabled'] = $this->excel_export_enabled;
        $data['advanced_reports_enabled'] = $this->advanced_reports_enabled;
        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            Plan::query()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث الباقة.');
        } else {
            $data['slug'] = $this->uniqueSlug($data['name']);
            Plan::query()->create($data);
            $this->toast('تم إنشاء الباقة.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $this->confirmThen(
            confirmEvent: 'plan-delete-confirmed',
            title: 'حذف الباقة؟',
            text: "لن تتمكن الباقات المرتبطة بمدرسين نشطين من التأثر، لكن لن يمكن اختيار \"{$plan->name}\" لمدرسين جدد.",
            params: ['planId' => $planId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('plan-delete-confirmed')]
    public function delete(int $planId): void
    {
        Plan::query()->findOrFail($planId)->delete();
        $this->toast('تم حذف الباقة.');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $suffix = 1;

        while (Plan::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.plans-index', [
            'plans' => Plan::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}

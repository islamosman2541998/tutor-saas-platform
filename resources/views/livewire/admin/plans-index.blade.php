<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">الباقات</h1>
            <p class="text-sm text-slate-500">الباقات المتاحة للمدرسين عند التسجيل والترقية</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ باقة جديدة</x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($plans as $plan)
            <div wire:key="plan-{{ $plan->id }}" class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-2 flex items-start justify-between">
                    <h3 class="font-bold">{{ $plan->name }}</h3>
                    @if ($plan->is_active)
                        <x-ui.badge color="green">مفعّلة</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">معطّلة</x-ui.badge>
                    @endif
                </div>

                <p class="mb-4 text-2xl font-bold text-indigo-600">
                    {{ $plan->monthly_price }} <span class="text-sm font-normal text-slate-400">/ شهريًا</span>
                </p>

                <ul class="mb-4 space-y-1 text-sm text-slate-600">
                    <li>الطلاب: {{ $plan->max_students ?? 'بلا حد' }}</li>
                    <li>المجموعات: {{ $plan->max_groups ?? 'بلا حد' }}</li>
                    <li>المستخدمون: {{ $plan->max_users ?? 'بلا حد' }}</li>
                    <li>الفترة التجريبية: {{ $plan->trial_days }} يوم</li>
                </ul>

                <div class="flex gap-2">
                    <button wire:click="edit({{ $plan->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        تعديل
                    </button>
                    <button wire:click="askDelete({{ $plan->id }})" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                        حذف
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-400">
                لا توجد باقات بعد. أنشئ أول باقة لعرضها للمدرسين الجدد.
            </div>
        @endforelse
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الباقة' : 'باقة جديدة'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم الباقة" wire:model="name" :error="$errors->first('name')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="monthly_price" type="number" label="السعر الشهري" wire:model="monthly_price" :error="$errors->first('monthly_price')" step="0.01" />
                <x-ui.input name="yearly_price" type="number" label="السعر السنوي (اختياري)" wire:model="yearly_price" :error="$errors->first('yearly_price')" step="0.01" />
            </div>

            <x-ui.input name="trial_days" type="number" label="أيام الفترة التجريبية" wire:model="trial_days" :error="$errors->first('trial_days')" />

            <div class="grid grid-cols-3 gap-4">
                <x-ui.input name="max_students" type="number" label="حد الطلاب" wire:model="max_students" />
                <x-ui.input name="max_groups" type="number" label="حد المجموعات" wire:model="max_groups" />
                <x-ui.input name="max_users" type="number" label="حد المستخدمين" wire:model="max_users" />
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="website_enabled" class="rounded border-slate-300 text-indigo-600"> الموقع التعريفي</label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="custom_domain_enabled" class="rounded border-slate-300 text-indigo-600"> دومين مخصص</label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="excel_export_enabled" class="rounded border-slate-300 text-indigo-600"> تصدير Excel</label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_reports_enabled" class="rounded border-slate-300 text-indigo-600"> تقارير متقدمة</label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600"> الباقة مفعّلة</label>
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

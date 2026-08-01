<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">إنشاء حساب مدرس جديد</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">للحسابات اللي بتوصلك بياناتها مباشرة — بيتفعّل فورًا من غير مراجعة.</p>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-bold text-slate-700 dark:text-slate-300">بيانات الحساب</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="teacher_name" label="اسم المدرس" wire:model="teacher_name" :error="$errors->first('teacher_name')" autofocus />
                <x-ui.input name="activity_name" label="اسم النشاط أو السنتر" wire:model="activity_name" :error="$errors->first('activity_name')" />
                <x-ui.input name="email" type="email" label="البريد الإلكتروني" wire:model="email" :error="$errors->first('email')" />
                <x-ui.input name="phone" label="رقم الهاتف (اختياري)" wire:model="phone" :error="$errors->first('phone')" />
                <x-ui.input name="password" type="password" label="كلمة المرور" wire:model="password" :error="$errors->first('password')" autocomplete="new-password" />
                <x-ui.input name="password_confirmation" type="password" label="تأكيد كلمة المرور" wire:model="password_confirmation" autocomplete="new-password" />
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-4 text-sm font-bold text-slate-700 dark:text-slate-300">الباقة والاشتراك</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الباقة</label>
                    <select wire:model.live="plan_id" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر باقة</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->monthly_price }} شهريًا)</option>
                        @endforeach
                    </select>
                    @error('plan_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">دورة الفوترة</label>
                    <select wire:model.live="billing_cycle" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="monthly">شهري</option>
                        <option value="yearly">سنوي</option>
                    </select>
                </div>

                <x-ui.input name="amount" type="number" label="المبلغ" wire:model="amount" :error="$errors->first('amount')" step="0.01" />
                <x-ui.input name="starts_at" type="date" label="تاريخ بداية الاشتراك" wire:model="starts_at" :error="$errors->first('starts_at')" />
            </div>
        </div>

        <div class="flex gap-3">
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">إنشاء الحساب</span>
                <span wire:loading wire:target="save">جارٍ الإنشاء...</span>
            </x-ui.button>
            <a href="{{ route('admin.tenants') }}" wire:navigate class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                إلغاء
            </a>
        </div>
    </form>
</div>

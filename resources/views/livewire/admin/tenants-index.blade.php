<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">المدرسون</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">إدارة حسابات المدرسين واشتراكاتهم</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <a
                href="{{ route('admin.tenants.create') }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                + إنشاء مدرس جديد
            </a>

            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث بالاسم أو النشاط أو البريد..."
                class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="pending">قيد المراجعة</option>
                <option value="active">نشط</option>
                <option value="suspended">موقوف</option>
                <option value="rejected">مرفوض</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">المدرس</th>
                    <th class="px-4 py-3 font-medium">النشاط</th>
                    <th class="px-4 py-3 font-medium">الباقة</th>
                    <th class="px-4 py-3 font-medium">حالة الحساب</th>
                    <th class="px-4 py-3 font-medium">تاريخ التسجيل</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($tenants as $tenant)
                    @php $subscription = $tenant->subscriptions->first(); @endphp
                    <tr wire:key="tenant-{{ $tenant->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $tenant->teacher_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $tenant->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $tenant->name }}</td>
                        <td class="px-4 py-3">
                            @if ($subscription)
                                <x-ui.badge :color="$subscription->isActive() ? 'indigo' : 'slate'">
                                    {{ $subscription->plan->name }}
                                </x-ui.badge>
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500">بدون اشتراك</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusBadge = match ($tenant->status) {
                                    'active' => ['green', 'نشط'],
                                    'pending' => ['amber', 'قيد المراجعة'],
                                    'rejected' => ['red', 'مرفوض'],
                                    default => ['red', 'موقوف'],
                                };
                            @endphp
                            <x-ui.badge :color="$statusBadge[0]">{{ $statusBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $tenant->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('tenant.login', ['tenant' => $tenant->slug]) }}"
                                    target="_blank"
                                    class="rounded-lg border border-indigo-200 px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50"
                                >
                                    صفحة الدخول
                                </a>

                                <button
                                    wire:click="openSubscriptionForm({{ $tenant->id }})"
                                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50"
                                >
                                    الاشتراك
                                </button>

                                <a
                                    href="{{ route('admin.subscriptions', ['search' => $tenant->teacher_name]) }}"
                                    wire:navigate
                                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50"
                                >
                                    سجل الاشتراكات
                                </a>

                                @if ($tenant->status === 'pending')
                                    <button
                                        wire:click="askToggleStatus({{ $tenant->id }}, 'active')"
                                        class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50"
                                    >
                                        قبول
                                    </button>
                                    <button
                                        wire:click="askToggleStatus({{ $tenant->id }}, 'rejected')"
                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                    >
                                        رفض
                                    </button>
                                @elseif ($tenant->status === 'active')
                                    <button
                                        wire:click="askToggleStatus({{ $tenant->id }}, 'suspended')"
                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                    >
                                        إيقاف
                                    </button>
                                @else
                                    <button
                                        wire:click="askToggleStatus({{ $tenant->id }}, 'active')"
                                        class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50"
                                    >
                                        تفعيل
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا يوجد مدرسون بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>

    <x-ui.modal :show="$managingTenantId !== null" title="إدارة اشتراك المدرس" on-close="closeSubscriptionForm">
        <form wire:submit="saveSubscription" class="space-y-4">
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">دورة الفوترة</label>
                    <select wire:model.live="billing_cycle" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="monthly">شهري</option>
                        <option value="yearly">سنوي</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">حالة الاشتراك</label>
                    <select wire:model="subscription_status" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="trial">تجريبي</option>
                        <option value="active">نشط</option>
                        <option value="expired">منتهي</option>
                        <option value="cancelled">ملغي</option>
                        <option value="suspended">موقوف</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="starts_at" type="date" label="تاريخ البداية" wire:model="starts_at" :error="$errors->first('starts_at')" />
                <x-ui.input name="ends_at" type="date" label="تاريخ النهاية (اختياري)" wire:model="ends_at" :error="$errors->first('ends_at')" />
            </div>

            <x-ui.input name="amount" type="number" label="المبلغ" wire:model="amount" :error="$errors->first('amount')" step="0.01" />

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ الاشتراك</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="closeSubscriptionForm">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

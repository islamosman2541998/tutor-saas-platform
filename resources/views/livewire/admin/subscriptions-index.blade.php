<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">المدفوعات والاشتراكات</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">كل اشتراكات المدرسين والدفعات الفعلية المسجّلة عليها</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث باسم المدرس أو النشاط..."
                class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="trial">تجريبي</option>
                <option value="active">نشط</option>
                <option value="expired">منتهي</option>
                <option value="cancelled">ملغي</option>
                <option value="suspended">موقوف</option>
            </select>
            <select wire:model.live="planFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الباقات</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[900px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">المدرس</th>
                    <th class="px-4 py-3 font-medium">الباقة</th>
                    <th class="px-4 py-3 font-medium">الفترة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">المبلغ</th>
                    <th class="px-4 py-3 font-medium">المدفوع</th>
                    <th class="px-4 py-3 font-medium">المتبقي</th>
                    <th class="px-4 py-3 font-medium">من - إلى</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($subscriptions as $subscription)
                    <tr wire:key="subscription-{{ $subscription->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $subscription->tenant->teacher_name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $subscription->tenant->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $subscription->plan->name }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $subscription->billing_cycle === 'monthly' ? 'شهري' : 'سنوي' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusBadge = match ($subscription->status) {
                                    'active' => ['green', 'نشط'],
                                    'trial' => ['indigo', 'تجريبي'],
                                    'expired' => ['slate', 'منتهي'],
                                    'cancelled' => ['red', 'ملغي'],
                                    default => ['red', 'موقوف'],
                                };
                            @endphp
                            <x-ui.badge :color="$statusBadge[0]">{{ $statusBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $subscription->amount, 2) }}</td>
                        <td class="px-4 py-3 text-emerald-600">{{ number_format($subscription->totalPaid(), 2) }}</td>
                        <td class="px-4 py-3 {{ $subscription->remainingAmount() > 0 ? 'text-red-600' : 'text-slate-400 dark:text-slate-500' }}">
                            {{ number_format($subscription->remainingAmount(), 2) }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                            {{ $subscription->starts_at->format('Y-m-d') }} - {{ $subscription->ends_at?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    wire:click="openPaymentForm({{ $subscription->id }})"
                                    class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50"
                                >
                                    تسجيل دفعة
                                </button>
                                <button
                                    wire:click="viewPayments({{ $subscription->id }})"
                                    class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50"
                                >
                                    سجل الدفعات ({{ $subscription->payments->count() }})
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا يوجد اشتراكات بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>

    <x-ui.modal :show="$recordingSubscriptionId !== null" title="تسجيل دفعة" on-close="closePaymentForm">
        <form wire:submit="savePayment" class="space-y-4">
            <x-ui.input name="amount" type="number" label="المبلغ" wire:model="amount" :error="$errors->first('amount')" step="0.01" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">طريقة الدفع</label>
                <select wire:model="payment_method" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="cash">نقدًا</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="wallet">محفظة إلكترونية</option>
                    <option value="card">بطاقة</option>
                    <option value="other">أخرى</option>
                </select>
                @error('payment_method') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-ui.input name="paid_at" type="date" label="تاريخ الدفع" wire:model="paid_at" :error="$errors->first('paid_at')" />
            <x-ui.input name="reference_number" label="رقم الإيصال (اختياري)" wire:model="reference_number" :error="$errors->first('reference_number')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">ملاحظات (اختياري)</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('notes') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ الدفعة</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="closePaymentForm">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$viewingSubscriptionId !== null" title="سجل الدفعات" on-close="closePaymentsHistory">
        @if ($viewingSubscription)
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                {{ $viewingSubscription->tenant->teacher_name }} — إجمالي المدفوع: {{ number_format($viewingSubscription->totalPaid(), 2) }} من {{ number_format((float) $viewingSubscription->amount, 2) }}
            </p>

            <div class="max-h-80 space-y-3 overflow-y-auto">
                @forelse ($viewingSubscription->payments as $payment)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ number_format((float) $payment->amount, 2) }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $payment->paid_at->format('Y-m-d') }}</span>
                        </div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $payment->methodLabel() }}
                            @if ($payment->reference_number)
                                — إيصال #{{ $payment->reference_number }}
                            @endif
                            @if ($payment->recordedBy)
                                — سجّلها {{ $payment->recordedBy->name }}
                            @endif
                        </div>
                        @if ($payment->notes)
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $payment->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-400 dark:text-slate-500">لسه مفيش دفعات مسجّلة.</p>
                @endforelse
            </div>
        @endif
    </x-ui.modal>
</div>

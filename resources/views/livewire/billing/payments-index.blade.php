<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">الدفعات</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">سجل الدفعات المستلمة من الطلاب وإيصالاتها</p>
        </div>

        @can('payments.record')
            <x-ui.button wire:click="openRecordForm" class="w-auto px-4">+ تسجيل دفعة</x-ui.button>
        @endcan
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
        <p class="text-xs font-medium text-slate-400 dark:text-slate-500">إجمالي الدفعات المكتملة (حسب الفلاتر الحالية)</p>
        <p class="mt-1 text-xl font-bold text-emerald-600">{{ number_format($totalCompleted, 2) }}</p>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="ابحث برقم الإيصال أو اسم/كود الطالب..."
            class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >

        <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل الحالات</option>
            <option value="completed">مكتملة</option>
            <option value="cancelled">ملغاة</option>
        </select>

        <select wire:model.live="methodFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل طرق الدفع</option>
            <option value="cash">نقدًا</option>
            <option value="bank_transfer">تحويل بنكي</option>
            <option value="wallet">محفظة إلكترونية</option>
            <option value="card">بطاقة</option>
            <option value="other">أخرى</option>
        </select>

        <input type="date" wire:model.live="fromDate" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        <input type="date" wire:model.live="toDate" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[900px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">رقم الإيصال</th>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">الاستحقاق</th>
                    <th class="px-4 py-3 font-medium">المبلغ</th>
                    <th class="px-4 py-3 font-medium">طريقة الدفع</th>
                    <th class="px-4 py-3 font-medium">تاريخ الدفع</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($payments as $payment)
                    <tr wire:key="payment-{{ $payment->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $payment->receipt_number }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $payment->student->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $payment->student->student_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            @if ($payment->monthlyDue)
                                {{ \App\Models\MonthlyDue::monthLabel($payment->monthlyDue->billing_month) }} {{ $payment->monthlyDue->billing_year }}
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500">رصيد مقدَّم</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->methodLabel() }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $payment->paid_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['completed' => 'green', 'cancelled' => 'red', 'refunded' => 'amber'];
                            @endphp
                            <x-ui.badge :color="$statusColors[$payment->status]">{{ $payment->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('tenant.payments.receipt', ['tenant' => $currentTenant->slug, 'payment' => $payment->id]) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    الإيصال
                                </a>
                                @can('payments.cancel')
                                    @if ($payment->status === 'completed')
                                        <button wire:click="openCancelForm({{ $payment->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                            إلغاء
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لا توجد دفعات مطابقة لهذه الفلاتر.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>

    <x-ui.modal :show="$showRecordForm" title="تسجيل دفعة" on-close="$set('showRecordForm', false)">
        <form wire:submit="recordPayment" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <div class="relative">
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">ابحث عن الطالب</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="studentSearch"
                    placeholder="اكتب اسم الطالب أو كوده..."
                    class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                @error('selectedStudentId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror

                @if ($searchResults->isNotEmpty())
                    <div class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg">
                        @foreach ($searchResults as $result)
                            <button
                                type="button"
                                wire:click="pickStudent({{ $result->id }})"
                                class="block w-full px-3.5 py-2 text-right text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50"
                            >
                                {{ $result->name }} <span class="text-xs text-slate-400 dark:text-slate-500">({{ $result->student_code }})</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($selectedStudentId)
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الاستحقاقات المستحقة</label>

                    @if ($outstandingDues->isEmpty())
                        <p class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-3 text-sm text-slate-500 dark:text-slate-400">
                            لا توجد استحقاقات غير مسددة لهذا الطالب.
                        </p>
                    @else
                        <div class="space-y-2">
                            @foreach ($outstandingDues as $due)
                                <div class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                                    <input type="checkbox" wire:model="selectedDues.{{ $due->id }}" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                                    <div class="flex-1 text-sm">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ \App\Models\MonthlyDue::monthLabel($due->billing_month) }} {{ $due->billing_year }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">المتبقي: {{ number_format((float) $due->remaining_amount, 2) }}</div>
                                    </div>
                                    <input
                                        type="number" step="0.01"
                                        wire:model="dueAmounts.{{ $due->id }}"
                                        class="w-28 rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                    >
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">طريقة الدفع</label>
                    <select wire:model="payment_method" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="cash">نقدًا</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="wallet">محفظة إلكترونية</option>
                        <option value="card">بطاقة</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <x-ui.input name="paid_at" type="datetime-local" label="تاريخ ووقت الدفع" wire:model="paid_at" :error="$errors->first('paid_at')" />
            </div>

            <x-ui.input name="reference_number" label="رقم مرجعي (اختياري)" wire:model="reference_number" :error="$errors->first('reference_number')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">ملاحظات (اختياري)</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('notes') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">مرفق إثبات الدفع (اختياري)</label>
                <input type="file" wire:model="attachment" class="block w-full text-sm">
                @error('attachment') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ الدفعة</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showRecordForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showCancelForm" title="إلغاء الدفعة" on-close="$set('showCancelForm', false)">
        <form wire:submit="saveCancel" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">سبب الإلغاء</label>
                <textarea wire:model="cancelReason" rows="3" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('cancelReason') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">تأكيد الإلغاء</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showCancelForm', false)">تراجع</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">المستحقات الشهرية</h1>
            <p class="text-sm text-slate-500">متابعة استحقاقات الطلاب الشهرية وتوليدها وإعفاؤها</p>
        </div>

        @can('payments.record')
            <x-ui.button wire:click="openGenerateForm" class="w-auto px-4">+ توليد استحقاقات الشهر</x-ui.button>
        @endcan
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-400">إجمالي المستحق (بعد الخصم)</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format((float) ($totals->final ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-400">إجمالي المدفوع</p>
            <p class="mt-1 text-xl font-bold text-emerald-600">{{ number_format((float) ($totals->paid ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-400">إجمالي المتبقي</p>
            <p class="mt-1 text-xl font-bold text-red-600">{{ number_format((float) ($totals->remaining ?? 0), 2) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <select wire:model.live="monthFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل الشهور</option>
            @foreach (range(1, 12) as $m)
                <option value="{{ $m }}">{{ \App\Models\MonthlyDue::monthLabel($m) }}</option>
            @endforeach
        </select>

        <select wire:model.live="yearFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @foreach (range(now()->year + 1, now()->year - 2) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل الحالات</option>
            <option value="unpaid">غير مدفوع</option>
            <option value="partially_paid">مدفوع جزئيًا</option>
            <option value="paid">مدفوع</option>
            <option value="waived">مُعفى</option>
            <option value="cancelled">ملغي</option>
        </select>

        <select wire:model.live="groupFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل المجموعات</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[880px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">المجموعة</th>
                    <th class="px-4 py-3 font-medium">الشهر</th>
                    <th class="px-4 py-3 font-medium">تاريخ الاستحقاق</th>
                    <th class="px-4 py-3 font-medium">المبلغ النهائي</th>
                    <th class="px-4 py-3 font-medium">المدفوع</th>
                    <th class="px-4 py-3 font-medium">المتبقي</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    @can('payments.cancel')
                        <th class="px-4 py-3 font-medium">إجراءات</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($dues as $due)
                    <tr wire:key="due-{{ $due->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $due->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $due->student->student_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $due->group->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ \App\Models\MonthlyDue::monthLabel($due->billing_month) }} {{ $due->billing_year }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $due->due_date->format('Y-m-d') }}
                            @if ($due->isOverdue())
                                <x-ui.badge color="red">متأخر</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format((float) $due->final_amount, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format((float) $due->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format((float) $due->remaining_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['unpaid' => 'red', 'partially_paid' => 'amber', 'paid' => 'green', 'waived' => 'indigo', 'cancelled' => 'slate'];
                            @endphp
                            <x-ui.badge :color="$statusColors[$due->status]">{{ $due->statusLabel() }}</x-ui.badge>
                        </td>
                        @can('payments.cancel')
                            <td class="px-4 py-3">
                                @if (! in_array($due->status, ['paid', 'cancelled', 'waived'], true) && (float) $due->paid_amount === 0.0)
                                    <button wire:click="openWaiveForm({{ $due->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        إعفاء / إلغاء
                                    </button>
                                @endif
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                            لا توجد استحقاقات مطابقة لهذه الفلاتر.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $dues->links() }}
    </div>

    <x-ui.modal :show="$showGenerateForm" title="توليد استحقاقات شهرية" on-close="$set('showGenerateForm', false)">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">الشهر</label>
                    <select wire:model="generateMonth" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \App\Models\MonthlyDue::monthLabel($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">السنة</label>
                    <select wire:model="generateYear" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        @foreach (range(now()->year - 1, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($preview === null)
                <x-ui.button wire:click="runPreview">معاينة قبل التوليد</x-ui.button>
            @else
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
                    سيتم توليد <strong>{{ $preview['count'] }}</strong> استحقاق جديد بإجمالي قيمة
                    <strong>{{ number_format((float) $preview['total'], 2) }}</strong>.
                    @if ($preview['count'] === 0)
                        <p class="mt-1 text-indigo-700">كل الاشتراكات المؤهلة لهذا الشهر لديها استحقاق بالفعل.</p>
                    @endif
                </div>

                <div class="flex gap-3 pt-2">
                    @if ($preview['count'] > 0)
                        <x-ui.button wire:click="confirmGenerate">تأكيد التوليد</x-ui.button>
                    @endif
                    <x-ui.button type="button" variant="secondary" wire:click="$set('showGenerateForm', false)">إغلاق</x-ui.button>
                </div>
            @endif
        </div>
    </x-ui.modal>

    <x-ui.modal :show="$showWaiveForm" title="إعفاء أو إلغاء استحقاق" on-close="$set('showWaiveForm', false)">
        <form wire:submit="saveWaive" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">الإجراء</label>
                <select wire:model="waiveStatus" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="waived">إعفاء (Waived)</option>
                    <option value="cancelled">إلغاء (Cancelled)</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">السبب</label>
                <textarea wire:model="waiveReason" rows="3" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('waiveReason') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showWaiveForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

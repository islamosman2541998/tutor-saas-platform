<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">المتأخرات</h1>
        <p class="text-sm text-slate-500">الاستحقاقات التي فات موعدها ولم تُسدَّد أو مسدَّدة جزئيًا</p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium text-red-500">عدد الاستحقاقات المتأخرة</p>
            <p class="mt-1 text-xl font-bold text-red-700">{{ (int) ($summary->count ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium text-red-500">إجمالي المبلغ المتأخر</p>
            <p class="mt-1 text-xl font-bold text-red-700">{{ number_format((float) ($summary->total_remaining ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium text-red-500">عدد الطلاب المتأثرين</p>
            <p class="mt-1 text-xl font-bold text-red-700">{{ (int) ($summary->students_count ?? 0) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="ابحث باسم أو كود الطالب..."
            class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >

        <select wire:model.live="groupFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل المجموعات</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="minDaysOverdue" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل مدد التأخير</option>
            <option value="7">أكثر من أسبوع</option>
            <option value="14">أكثر من أسبوعين</option>
            <option value="30">أكثر من شهر</option>
            <option value="60">أكثر من شهرين</option>
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
                    <th class="px-4 py-3 font-medium">أيام التأخير</th>
                    <th class="px-4 py-3 font-medium">المتبقي</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($dues as $due)
                    <tr wire:key="overdue-{{ $due->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $due->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $due->student->student_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $due->group->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ \App\Models\MonthlyDue::monthLabel($due->billing_month) }} {{ $due->billing_year }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $due->due_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="red">{{ (int) $due->days_overdue }} يوم</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ number_format((float) $due->remaining_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @can('payments.record')
                                    <a href="{{ route('tenant.payments', ['tenant' => $currentTenant->slug, 'student' => $due->student_id]) }}" class="rounded-lg border border-indigo-200 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                        تسجيل دفعة
                                    </a>
                                @endcan
                                @can('payments.cancel')
                                    <button wire:click="openWaiveForm({{ $due->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        إعفاء / إلغاء
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            لا توجد استحقاقات متأخرة مطابقة لهذه الفلاتر.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $dues->links() }}
    </div>

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

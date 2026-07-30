<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" class="mb-1 inline-block text-sm text-indigo-600 hover:underline">← المجموعات</a>
            <h1 class="text-2xl font-bold">طلاب "{{ $group->name }}"</h1>
            <p class="text-sm text-slate-500">
                {{ $enrollments->count() }}{{ $group->capacity ? ' / '.$group->capacity : '' }} طالب مشترك
                @if ($group->isFull())
                    <span class="text-amber-600">(مكتملة)</span>
                @endif
            </p>
        </div>

        <x-ui.button wire:click="openEnrollForm" class="w-auto px-4">+ اشتراك طالب</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">السعر الشهري</th>
                    <th class="px-4 py-3 font-medium">تاريخ الاشتراك</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($enrollments as $enrollment)
                    <tr wire:key="enrollment-{{ $enrollment->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $enrollment->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $enrollment->student->student_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $enrollment->final_monthly_price }}
                            @if ($enrollment->discount_type !== 'none')
                                <span class="text-xs text-emerald-600">(بعد خصم)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $enrollment->joined_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="askWithdraw({{ $enrollment->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                إنهاء الاشتراك
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                            لا يوجد طلاب مشتركون في هذه المجموعة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($waitingList->isNotEmpty())
        <div class="mt-6">
            <h2 class="mb-3 text-lg font-bold">قائمة الانتظار</h2>
            <div class="overflow-x-auto rounded-2xl border border-amber-200 bg-amber-50">
                <table class="w-full min-w-[560px] text-right text-sm">
                    <thead class="border-b border-amber-200 text-amber-800">
                        <tr>
                            <th class="px-4 py-3 font-medium">الطالب</th>
                            <th class="px-4 py-3 font-medium">تاريخ الطلب</th>
                            <th class="px-4 py-3 font-medium">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-200">
                        @foreach ($waitingList as $entry)
                            <tr wire:key="waiting-{{ $entry->id }}">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $entry->student->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $entry->requested_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <button wire:click="convertFromWaitlist({{ $entry->id }})" class="rounded-lg border border-emerald-300 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50">
                                        تحويل لاشتراك
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <x-ui.modal :show="$showEnrollForm" title="اشتراك طالب في المجموعة" on-close="$set('showEnrollForm', false)">
        <form wire:submit="enroll" class="space-y-4">
            <div class="relative">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">ابحث عن الطالب</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="studentSearch"
                    placeholder="اكتب اسم الطالب أو كوده..."
                    class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                @error('selectedStudentId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror

                @if ($searchResults->isNotEmpty())
                    <div class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                        @foreach ($searchResults as $result)
                            <button
                                type="button"
                                wire:click="pickStudent({{ $result->id }})"
                                class="block w-full px-3.5 py-2 text-right text-sm hover:bg-slate-50"
                            >
                                {{ $result->name }} <span class="text-xs text-slate-400">({{ $result->student_code }})</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <x-ui.input name="custom_monthly_price" type="number" label="سعر مخصص (اختياري، غير سعر المجموعة الافتراضي)" wire:model="custom_monthly_price" step="0.01" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">نوع الخصم</label>
                    <select wire:model="discount_type" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="none">بدون خصم</option>
                        <option value="fixed">مبلغ ثابت</option>
                        <option value="percentage">نسبة مئوية</option>
                    </select>
                </div>
                <x-ui.input name="discount_value" type="number" label="قيمة الخصم" wire:model="discount_value" step="0.01" />
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">اشتراك</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showEnrollForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

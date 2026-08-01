<div>
    <a href="{{ route('tenant.students', ['tenant' => $currentTenant->slug]) }}" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">← الطلاب</a>

    <div class="mb-6 flex items-center gap-4">
        @if ($student->imageUrl())
            <img src="{{ $student->imageUrl() }}" class="h-16 w-16 rounded-full object-cover" alt="">
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-2xl font-medium text-indigo-600">
                {{ mb_substr($student->name, 0, 1) }}
            </span>
        @endif
        <div>
            <h1 class="text-2xl font-bold">{{ $student->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $student->student_code }} — {{ $student->statusLabel() }}</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-3 font-bold">بيانات التواصل</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">الهاتف</dt><dd>{{ $student->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">البريد الإلكتروني</dt><dd>{{ $student->email ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">ولي الأمر</dt><dd>{{ $student->guardian_name ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">هاتف ولي الأمر</dt><dd>{{ $student->guardian_phone ?: '—' }}</dd></div>
            </dl>
            @if ($student->whatsappUrl())
                <a href="{{ $student->whatsappUrl() }}" target="_blank" class="mt-3 inline-block rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    تواصل عبر واتساب
                </a>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
            <h2 class="mb-3 font-bold">بيانات إضافية</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">المدرسة</dt><dd>{{ $student->school_name ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">المحافظة</dt><dd>{{ $student->governorate ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500 dark:text-slate-400">تاريخ الانضمام</dt><dd>{{ $student->joined_at->format('Y-m-d') }}</dd></div>
            </dl>
            @if ($student->notes)
                <p class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 p-3 text-xs text-slate-600 dark:text-slate-400">{{ $student->notes }}</p>
            @endif
        </div>
    </div>

    <h2 class="mb-3 text-lg font-bold">الاشتراكات</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">المجموعة</th>
                    <th class="px-4 py-3 font-medium">السعر</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">تاريخ الاشتراك</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($enrollments as $enrollment)
                    <tr wire:key="enr-{{ $enrollment->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $enrollment->group->name }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $enrollment->final_monthly_price }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colors = ['active' => 'green', 'paused' => 'amber', 'withdrawn' => 'red', 'completed' => 'indigo', 'transferred' => 'slate'];
                            @endphp
                            <x-ui.badge :color="$colors[$enrollment->status]">{{ $enrollment->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $enrollment->joined_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @if ($enrollment->status === 'active')
                                <button wire:click="openTransferForm({{ $enrollment->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    نقل مجموعة
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            الطالب غير مشترك في أي مجموعة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showTransferForm" title="نقل الطالب لمجموعة أخرى" on-close="$set('showTransferForm', false)">
        <form wire:submit="transfer" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">المجموعة الجديدة</label>
                <select wire:model="to_group_id" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">اختر مجموعة</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                @error('to_group_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">نقل</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showTransferForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

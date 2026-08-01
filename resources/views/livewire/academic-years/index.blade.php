<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">السنوات الدراسية</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">عام دراسي واحد فقط يكون "حاليًا" في نفس الوقت</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ عام دراسي جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[640px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">الاسم</th>
                    <th class="px-4 py-3 font-medium">الفترة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($years as $year)
                    <tr wire:key="year-{{ $year->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $year->name }}</div>
                            @if ($year->is_current)
                                <x-ui.badge color="indigo">العام الحالي</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ $year->start_date->format('Y-m-d') }} → {{ $year->end_date->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($year->status === 'active')
                                <x-ui.badge color="green">نشط</x-ui.badge>
                            @elseif ($year->status === 'closed')
                                <x-ui.badge color="amber">مغلق</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">مؤرشف</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @if (! $year->is_current && $year->status === 'active')
                                    <button wire:click="askSetCurrent({{ $year->id }})" class="rounded-lg border border-indigo-200 px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                        تحديد كحالي
                                    </button>
                                @endif

                                @if ($year->status === 'active')
                                    <button wire:click="askClose({{ $year->id }})" class="rounded-lg border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50">
                                        إغلاق
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُنشئ أي عام دراسي بعد. أنشئ عامك الأول لتبدأ في إضافة المجموعات.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" title="عام دراسي جديد" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم العام الدراسي" wire:model="name" :error="$errors->first('name')" placeholder="مثال: 2026 / 2027" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="start_date" type="date" label="تاريخ البداية" wire:model="start_date" :error="$errors->first('start_date')" />
                <x-ui.input name="end_date" type="date" label="تاريخ النهاية" wire:model="end_date" :error="$errors->first('end_date')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="make_current" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                تحديده كعام دراسي حالي
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

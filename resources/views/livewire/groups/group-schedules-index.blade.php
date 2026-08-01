<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" class="mb-1 inline-block text-sm text-indigo-600 hover:underline">← المجموعات</a>
            <h1 class="text-2xl font-bold">مواعيد "{{ $group->name }}"</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $group->subject->name }} — {{ $group->grade->name }} — {{ $group->location->name }}</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ موعد جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[560px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">اليوم</th>
                    <th class="px-4 py-3 font-medium">الوقت</th>
                    <th class="px-4 py-3 font-medium">المكان</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($schedules as $schedule)
                    <tr wire:key="schedule-{{ $schedule->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $schedule->dayLabel() }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $schedule->location->name ?? $group->location->name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $schedule->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $schedule->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لا توجد مواعيد لهذه المجموعة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الموعد' : 'موعد جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">اليوم</label>
                <select wire:model="day_of_week" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="0">الأحد</option>
                    <option value="1">الإثنين</option>
                    <option value="2">الثلاثاء</option>
                    <option value="3">الأربعاء</option>
                    <option value="4">الخميس</option>
                    <option value="5">الجمعة</option>
                    <option value="6">السبت</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="start_time" type="time" label="وقت البداية" wire:model="start_time" :error="$errors->first('start_time')" />
                <x-ui.input name="end_time" type="time" label="وقت النهاية" wire:model="end_time" :error="$errors->first('end_time')" />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">مكان مختلف (اختياري)</label>
                <select wire:model="location_id" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">نفس مكان المجموعة ({{ $group->location->name }})</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
                @error('location_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

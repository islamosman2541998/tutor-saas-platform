<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">المراحل التعليمية</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">مثل: ابتدائي، إعدادي، ثانوي</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ مرحلة جديدة</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[640px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">المرحلة</th>
                    <th class="px-4 py-3 font-medium">عدد الصفوف</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($stages as $index => $stage)
                    <tr wire:key="stage-{{ $stage->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $stage->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $stage->id }})" @if($index === $stages->count() - 1) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $stage->name }}</div>
                            @if ($stage->description)
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $stage->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $stage->grades_count }}</td>
                        <td class="px-4 py-3">
                            @if ($stage->is_active)
                                <x-ui.badge color="green">مفعّلة</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّلة</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('tenant.stages.grades', ['tenant' => $currentTenant->slug, 'stage' => $stage->id]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    الصفوف
                                </a>
                                <button wire:click="edit({{ $stage->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $stage->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُنشئ أي مرحلة تعليمية بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل المرحلة' : 'مرحلة جديدة'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم المرحلة" wire:model="name" :error="$errors->first('name')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">وصف مختصر (اختياري)</label>
                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                مرحلة مفعّلة
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

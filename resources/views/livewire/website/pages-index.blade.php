<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">الصفحات الثابتة</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">صفحات مثل "سياسة الخصوصية" أو "الشروط والأحكام" — تُضاف لشريط التنقّل أو الفوتر عند تفعيلها</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ صفحة جديدة</x-ui.button>
    </div>

    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="ابحث بالعنوان..."
            class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">العنوان</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">تظهر في</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($pages as $page)
                    <tr wire:key="page-{{ $page->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $page->title }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$page->status === 'published' ? 'green' : 'slate'">{{ $page->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            @if ($page->show_in_navbar) <span>شريط التنقّل</span> @endif
                            @if ($page->show_in_navbar && $page->show_in_footer) · @endif
                            @if ($page->show_in_footer) <span>الفوتر</span> @endif
                            @if (! $page->show_in_navbar && ! $page->show_in_footer) — @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $page->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $page->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُنشئ أي صفحة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pages->links() }}
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الصفحة' : 'صفحة جديدة'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <x-ui.input name="title" label="العنوان" wire:model="title" :error="$errors->first('title')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">المحتوى</label>
                <textarea wire:model="content" rows="10" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('content') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الحالة</label>
                <select wire:model="status" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="draft">مسودة</option>
                    <option value="published">منشورة</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="show_in_navbar" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                إظهار في شريط التنقّل
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="show_in_footer" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                إظهار في الفوتر
            </label>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="meta_title" label="عنوان SEO (اختياري)" wire:model="meta_title" :error="$errors->first('meta_title')" />
                <x-ui.input name="meta_description" label="وصف SEO (اختياري)" wire:model="meta_description" :error="$errors->first('meta_description')" />
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

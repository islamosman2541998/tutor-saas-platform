<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">شريط التنقّل (Navbar)</h1>
            <p class="text-sm text-slate-500">الروابط التي تظهر أعلى موقعك التعريفي</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ عنصر جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">التسمية</th>
                    <th class="px-4 py-3 font-medium">النوع</th>
                    <th class="px-4 py-3 font-medium">الوجهة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $index => $item)
                    <tr wire:key="navbar-item-{{ $item->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $item->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $item->id }})" @if($index === $items->count() - 1) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $item->label }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $item->typeLabel() }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            @if ($item->type === 'external')
                                {{ $item->url }}
                            @elseif ($item->type === 'page')
                                {{ optional($pages->firstWhere('slug', $item->target_key))->title ?? $item->target_key }}
                            @else
                                {{ $systemLinks[$item->target_key] ?? $item->target_key }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->is_visible)
                                <x-ui.badge color="green">ظاهر</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">مخفي</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $item->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $item->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            لم تُضِف أي عنصر لشريط التنقّل بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل العنصر' : 'عنصر جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="label" label="التسمية" wire:model="label" :error="$errors->first('label')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">النوع</label>
                <select wire:model.live="type" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="section">رابط من النظام</option>
                    <option value="page">صفحة أنشأتها</option>
                    <option value="external">رابط خارجي</option>
                </select>
            </div>

            @if ($type === 'external')
                <x-ui.input name="url" label="الرابط" wire:model="url" placeholder="https://..." :error="$errors->first('url')" />
            @elseif ($type === 'page')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">الصفحة</label>
                    <select wire:model="target_key" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر صفحة</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->slug }}">{{ $page->title }}{{ $page->status !== 'published' ? ' (مسودة)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('target_key') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">رابط النظام</label>
                    <select wire:model="target_key" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">اختر رابط</option>
                        @foreach ($systemLinks as $key => $systemLabel)
                            <option value="{{ $key }}">{{ $systemLabel }}</option>
                        @endforeach
                    </select>
                    @error('target_key') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="open_in_new_tab" class="rounded border-slate-300 text-indigo-600">
                فتح في تبويب جديد
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="is_visible" class="rounded border-slate-300 text-indigo-600">
                ظاهر في شريط التنقّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

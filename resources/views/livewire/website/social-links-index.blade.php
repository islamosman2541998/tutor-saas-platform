<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">روابط التواصل الاجتماعي</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">تظهر في الشريط العلوي و/أو الفوتر حسب ما تحدده</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ رابط جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">المنصة</th>
                    <th class="px-4 py-3 font-medium">الرابط</th>
                    <th class="px-4 py-3 font-medium">مكان الظهور</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($links as $index => $link)
                    <tr wire:key="social-link-{{ $link->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $link->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $link->id }})" @if($index === $links->count() - 1) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $link->platformLabel() }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs max-w-[220px] truncate">{{ $link->url }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $link->displayLocationLabel() }}</td>
                        <td class="px-4 py-3">
                            @if ($link->is_active)
                                <x-ui.badge color="green">مفعّل</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّل</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $link->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $link->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُضِف أي رابط تواصل اجتماعي بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الرابط' : 'رابط جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">المنصة</label>
                <select wire:model="platform" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="facebook">فيسبوك</option>
                    <option value="instagram">إنستجرام</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="youtube">يوتيوب</option>
                    <option value="whatsapp">واتساب</option>
                    <option value="telegram">تليجرام</option>
                    <option value="linkedin">لينكدإن</option>
                    <option value="x">X (تويتر)</option>
                    <option value="other">أخرى</option>
                </select>
            </div>

            <x-ui.input name="url" label="الرابط" wire:model="url" placeholder="https://..." :error="$errors->first('url')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">مكان الظهور</label>
                <select wire:model="display_location" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="all">كل الأماكن</option>
                    <option value="navbar">الشريط العلوي فقط</option>
                    <option value="footer">الفوتر فقط</option>
                    <option value="contact">قسم التواصل فقط</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                رابط مفعّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

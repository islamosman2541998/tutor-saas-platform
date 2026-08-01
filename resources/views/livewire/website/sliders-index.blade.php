<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">السلايدر الرئيسي</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">الصور المتحركة في أعلى الصفحة الرئيسية لموقعك</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ سلايد جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[800px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">الصورة</th>
                    <th class="px-4 py-3 font-medium">العنوان</th>
                    <th class="px-4 py-3 font-medium">الفترة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($sliders as $index => $slider)
                    <tr wire:key="slider-{{ $slider->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $slider->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $slider->id }})" @if($index === $sliders->count() - 1) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <img src="{{ $slider->imageUrl() }}" class="h-12 w-20 rounded object-cover">
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $slider->title ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            {{ $slider->start_date?->format('Y-m-d') ?? 'بدون بداية' }} — {{ $slider->end_date?->format('Y-m-d') ?? 'بدون نهاية' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($slider->is_active)
                                <x-ui.badge color="green">مفعّل</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّل</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $slider->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $slider->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُضِف أي سلايد بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل السلايد' : 'سلايد جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_auto]">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الصورة (سطح المكتب)</label>
                    <label
                        for="slider-image"
                        class="group relative flex h-40 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10"
                    >
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover">
                        @elseif ($existingImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}" class="absolute inset-0 h-full w-full object-cover">
                        @endif

                        @if ($image || $existingImage)
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                <span class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-700">تغيير الصورة</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1.5 text-slate-400 dark:text-slate-500">
                                <x-ui.icon name="image" class="h-7 w-7" />
                                <span class="text-xs font-medium">اضغط لاختيار صورة</span>
                                <span class="text-[11px]">يفضّل مقاس أفقي عريض</span>
                            </div>
                        @endif

                        <input id="slider-image" type="file" wire:model="image" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('image') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الصورة (موبايل، اختياري)</label>
                    <label
                        for="slider-mobile-image"
                        class="group relative mx-auto flex h-40 w-24 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 transition hover:border-indigo-400 hover:bg-indigo-50/50 dark:border-slate-600 dark:bg-slate-900/30 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10 sm:mx-0"
                    >
                        @if ($mobile_image)
                            <img src="{{ $mobile_image->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover">
                        @elseif ($existingMobileImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingMobileImage) }}" class="absolute inset-0 h-full w-full object-cover">
                        @endif

                        @if ($mobile_image || $existingMobileImage)
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                                <span class="rounded-lg bg-white/90 px-2 py-1 text-[11px] font-medium text-slate-700">تغيير</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center gap-1 px-1 text-center text-slate-400 dark:text-slate-500">
                                <x-ui.icon name="image" class="h-6 w-6" />
                                <span class="text-[11px] font-medium">اختياري</span>
                            </div>
                        @endif

                        <input id="slider-mobile-image" type="file" wire:model="mobile_image" accept="image/*" class="sr-only">
                    </label>
                    <div wire:loading wire:target="mobile_image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>
                    @error('mobile_image') <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-ui.input name="title" label="العنوان (اختياري)" wire:model="title" :error="$errors->first('title')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">الوصف (اختياري)</label>
                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="button_text" label="نص الزر (اختياري)" wire:model="button_text" :error="$errors->first('button_text')" />
                <x-ui.input name="button_url" label="رابط الزر" wire:model="button_url" placeholder="https://..." :error="$errors->first('button_url')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="start_date" type="date" label="تاريخ البداية (اختياري)" wire:model="start_date" />
                <x-ui.input name="end_date" type="date" label="تاريخ النهاية (اختياري)" wire:model="end_date" :error="$errors->first('end_date')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="open_in_new_tab" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                فتح الزر في تبويب جديد
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                سلايد مفعّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

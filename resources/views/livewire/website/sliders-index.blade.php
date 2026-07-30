<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">السلايدر الرئيسي</h1>
            <p class="text-sm text-slate-500">الصور المتحركة في أعلى الصفحة الرئيسية لموقعك</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ سلايد جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[800px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">الصورة</th>
                    <th class="px-4 py-3 font-medium">العنوان</th>
                    <th class="px-4 py-3 font-medium">الفترة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sliders as $index => $slider)
                    <tr wire:key="slider-{{ $slider->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $slider->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $slider->id }})" @if($index === $sliders->count() - 1) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <img src="{{ $slider->imageUrl() }}" class="h-12 w-20 rounded object-cover">
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $slider->title ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">
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
                                <button wire:click="edit({{ $slider->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
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
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            لم تُضِف أي سلايد بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل السلايد' : 'سلايد جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">الصورة (سطح المكتب)</label>
                <input type="file" wire:model="image" class="block w-full text-sm">
                @if ($existingImage && ! $image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}" class="mt-2 h-16 rounded border border-slate-200 object-cover">
                @endif
                @error('image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">الصورة (موبايل، اختياري)</label>
                <input type="file" wire:model="mobile_image" class="block w-full text-sm">
                @if ($existingMobileImage && ! $mobile_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingMobileImage) }}" class="mt-2 h-16 rounded border border-slate-200 object-cover">
                @endif
                @error('mobile_image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-ui.input name="title" label="العنوان (اختياري)" wire:model="title" :error="$errors->first('title')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">الوصف (اختياري)</label>
                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="button_text" label="نص الزر (اختياري)" wire:model="button_text" :error="$errors->first('button_text')" />
                <x-ui.input name="button_url" label="رابط الزر" wire:model="button_url" placeholder="https://..." :error="$errors->first('button_url')" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="start_date" type="date" label="تاريخ البداية (اختياري)" wire:model="start_date" />
                <x-ui.input name="end_date" type="date" label="تاريخ النهاية (اختياري)" wire:model="end_date" :error="$errors->first('end_date')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="open_in_new_tab" class="rounded border-slate-300 text-indigo-600">
                فتح الزر في تبويب جديد
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600">
                سلايد مفعّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

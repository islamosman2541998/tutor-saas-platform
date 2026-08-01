<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">آراء الطلاب</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">شهادات الطلاب وأولياء الأمور التي تظهر في موقعك التعريفي</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ رأي جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[800px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">الرأي</th>
                    <th class="px-4 py-3 font-medium">التقييم</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($testimonials as $index => $testimonial)
                    <tr wire:key="testimonial-{{ $testimonial->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $testimonial->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $testimonial->id }})" @if($index === $testimonials->count() - 1) disabled @endif class="rounded border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 text-xs text-slate-500 dark:text-slate-400 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($testimonial->studentImageUrl())
                                    <img src="{{ $testimonial->studentImageUrl() }}" class="h-9 w-9 rounded-full object-cover">
                                @endif
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $testimonial->student_name }}</div>
                                    @if ($testimonial->grade_or_group)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $testimonial->grade_or_group }}</div>
                                    @endif
                                </div>
                                @if ($testimonial->is_featured)
                                    <span title="مميز">⭐</span>
                                @endif
                            </div>
                        </td>
                        <td class="max-w-[280px] truncate px-4 py-3 text-slate-600 dark:text-slate-400">{{ $testimonial->content }}</td>
                        <td class="px-4 py-3 text-amber-500">{{ str_repeat('★', $testimonial->rating ?? 0) }}{{ str_repeat('☆', 5 - ($testimonial->rating ?? 0)) }}</td>
                        <td class="px-4 py-3">
                            @if ($testimonial->is_active)
                                <x-ui.badge color="green">مفعّل</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّل</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $testimonial->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $testimonial->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">
                            لم تُضِف أي رأي طالب بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الرأي' : 'رأي جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <x-ui.input name="student_name" label="اسم الطالب" wire:model="student_name" :error="$errors->first('student_name')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة الطالب (اختياري)</label>
                <input type="file" wire:model="student_image" class="block w-full text-sm">
                @if ($existingImage && ! $student_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}" class="mt-2 h-12 w-12 rounded-full border border-slate-200 dark:border-slate-700 object-cover">
                @endif
                @error('student_image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">نص الرأي</label>
                <textarea wire:model="content" rows="4" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('content') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">التقييم</label>
                    <select wire:model="rating" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4)</option>
                        <option value="3">★★★☆☆ (3)</option>
                        <option value="2">★★☆☆☆ (2)</option>
                        <option value="1">★☆☆☆☆ (1)</option>
                    </select>
                </div>
                <x-ui.input name="grade_or_group" label="الصف أو المجموعة (اختياري)" wire:model="grade_or_group" :error="$errors->first('grade_or_group')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_featured" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                رأي مميز
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                مفعّل (يظهر في الموقع)
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">المواد الدراسية</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">المواد التي تُدرّسها، وتُستخدم عند إنشاء المجموعات</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ مادة جديدة</x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($subjects as $subject)
            <div wire:key="subject-{{ $subject->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5">
                <div class="mb-3 flex items-center gap-3">
                    @if ($subject->imageUrl())
                        <img src="{{ $subject->imageUrl() }}" class="h-12 w-12 rounded-lg object-cover" alt="">
                    @elseif ($subject->icon)
                        <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-2xl">{{ $subject->icon }}</span>
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:text-slate-500">؟</span>
                    @endif

                    <div>
                        <h3 class="font-bold">{{ $subject->name }}</h3>
                        @if ($subject->is_active)
                            <x-ui.badge color="green">مفعّلة</x-ui.badge>
                        @else
                            <x-ui.badge color="slate">معطّلة</x-ui.badge>
                        @endif
                    </div>
                </div>

                @if ($subject->description)
                    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ $subject->description }}</p>
                @endif

                <div class="flex gap-2">
                    <button wire:click="edit({{ $subject->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        تعديل
                    </button>
                    <button wire:click="askDelete({{ $subject->id }})" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                        حذف
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-slate-400 dark:text-slate-500">
                لم تُضف أي مادة دراسية بعد.
            </div>
        @endforelse
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل المادة' : 'مادة جديدة'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم المادة" wire:model="name" :error="$errors->first('name')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="icon" label="رمز مختصر (اختياري)" wire:model="icon" :error="$errors->first('icon')" placeholder="مثال: 📐" />

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">صورة (اختياري)</label>
                    <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-slate-600 dark:text-slate-400">
                    @error('image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div wire:loading wire:target="image" class="mt-1 text-xs text-slate-400 dark:text-slate-500">جارٍ الرفع...</div>

                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="mt-2 h-16 w-16 rounded-lg object-cover">
                    @elseif ($existingImagePath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImagePath) }}" class="mt-2 h-16 w-16 rounded-lg object-cover">
                    @endif
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">وصف مختصر (اختياري)</label>
                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                مادة مفعّلة
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

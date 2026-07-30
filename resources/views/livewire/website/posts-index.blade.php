<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">النصائح والمقالات</h1>
            <p class="text-sm text-slate-500">محتوى تعليمي يظهر في قسم "نصائح تهمّك" بموقعك التعريفي</p>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ مقال جديد</x-ui.button>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="ابحث بالعنوان..."
            class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
        <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل الحالات</option>
            <option value="draft">مسودة</option>
            <option value="published">منشور</option>
            <option value="archived">مؤرشف</option>
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[800px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">المقال</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">تاريخ النشر</th>
                    <th class="px-4 py-3 font-medium">مميز</th>
                    <th class="px-4 py-3 font-medium">المشاهدات</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($posts as $post)
                    <tr wire:key="post-{{ $post->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($post->imageUrl())
                                    <img src="{{ $post->imageUrl() }}" class="h-10 w-14 rounded object-cover">
                                @endif
                                <span class="font-medium text-slate-900">{{ $post->title }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['draft' => 'slate', 'published' => 'green', 'archived' => 'amber'];
                            @endphp
                            <x-ui.badge :color="$statusColors[$post->status]">{{ $post->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $post->published_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $post->is_featured ? '⭐' : '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $post->views_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $post->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $post->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            لم تُنشئ أي مقال بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل المقال' : 'مقال جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="max-h-[75vh] space-y-4 overflow-y-auto pe-1">
            <x-ui.input name="title" label="العنوان" wire:model="title" :error="$errors->first('title')" />

            <x-ui.input name="excerpt" label="ملخص قصير (اختياري، يظهر في القوائم)" wire:model="excerpt" :error="$errors->first('excerpt')" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">المحتوى</label>
                <textarea wire:model="content" rows="8" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                @error('content') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">صورة الغلاف (اختياري)</label>
                <input type="file" wire:model="image" class="block w-full text-sm">
                @if ($existingImage && ! $image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}" class="mt-2 h-16 rounded border border-slate-200 object-cover">
                @endif
                @error('image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">الحالة</label>
                    <select wire:model="status" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="draft">مسودة</option>
                        <option value="published">منشور</option>
                        <option value="archived">مؤرشف</option>
                    </select>
                </div>
                <x-ui.input name="published_at" type="datetime-local" label="تاريخ النشر (اختياري)" wire:model="published_at" :error="$errors->first('published_at')" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="is_featured" class="rounded border-slate-300 text-indigo-600">
                مقال مميز (يظهر أولًا في الصفحة الرئيسية)
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

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('tenant.stages', ['tenant' => $currentTenant->slug]) }}" class="mb-1 inline-block text-sm text-indigo-600 hover:underline">← المراحل</a>
            <h1 class="text-2xl font-bold">صفوف "{{ $stage->name }}"</h1>
        </div>

        <x-ui.button wire:click="create" class="w-auto px-4">+ صف جديد</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[480px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">الصف</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($grades as $grade)
                    <tr wire:key="grade-{{ $grade->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $grade->name }}</td>
                        <td class="px-4 py-3">
                            @if ($grade->is_active)
                                <x-ui.badge color="green">مفعّل</x-ui.badge>
                            @else
                                <x-ui.badge color="slate">معطّل</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $grade->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    تعديل
                                </button>
                                <button wire:click="askDelete({{ $grade->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-slate-400">
                            لا توجد صفوف في هذه المرحلة بعد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" :title="$editingId ? 'تعديل الصف' : 'صف جديد'" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="name" label="اسم الصف" wire:model="name" :error="$errors->first('name')" placeholder="مثال: الصف الأول الإعدادي" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600">
                صف مفعّل
            </label>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

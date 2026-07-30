<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">أقسام الصفحة الرئيسية</h1>
        <p class="text-sm text-slate-500">تحكّم في ترتيب وظهور وعناوين أقسام موقعك التعريفي</p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="w-24 px-4 py-3 font-medium">الترتيب</th>
                    <th class="px-4 py-3 font-medium">القسم</th>
                    <th class="px-4 py-3 font-medium">العنوان المخصّص</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sections as $index => $section)
                    <tr wire:key="section-{{ $section->id }}">
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button wire:click="moveUp({{ $section->id }})" @if($index === 0) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↑</button>
                                <button wire:click="moveDown({{ $section->id }})" @if($index === $sections->count() - 1) disabled @endif class="rounded border border-slate-200 px-1.5 py-0.5 text-xs text-slate-500 disabled:opacity-30">↓</button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $section->label() }}</div>
                            @unless ($section->isReady())
                                <div class="text-xs text-amber-600">قريبًا — لا يظهر في الموقع بعد</div>
                            @endunless
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $section->title ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleVisibility({{ $section->id }})">
                                @if ($section->is_visible)
                                    <x-ui.badge color="green">ظاهر</x-ui.badge>
                                @else
                                    <x-ui.badge color="slate">مخفي</x-ui.badge>
                                @endif
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="edit({{ $section->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                تعديل العنوان
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                            جارٍ تجهيز الأقسام...
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showForm" title="تعديل عنوان القسم" on-close="$set('showForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="title" label="العنوان (اختياري — يظل الافتراضي لو تُرك فارغًا)" wire:model="title" :error="$errors->first('title')" />
            <x-ui.input name="subtitle" label="العنوان الفرعي (اختياري)" wire:model="subtitle" :error="$errors->first('subtitle')" />

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

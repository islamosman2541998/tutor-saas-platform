<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <a href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" class="mb-1 inline-block text-sm text-indigo-600 hover:underline">← المجموعات</a>
            <h1 class="text-2xl font-bold">حصص "{{ $group->name }}"</h1>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
                <select wire:model="weeksAhead" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="2">أسبوعين</option>
                    <option value="4">4 أسابيع</option>
                    <option value="8">8 أسابيع</option>
                </select>
                <x-ui.button wire:click="generate" class="w-auto px-4">توليد من الجدول</x-ui.button>
            </div>
            <x-ui.button wire:click="openManualForm" variant="secondary" class="w-auto px-4">+ حصة يدوية</x-ui.button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[720px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">التاريخ</th>
                    <th class="px-4 py-3 font-medium">الوقت</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sessions as $session)
                    <tr wire:key="session-{{ $session->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $session->scheduled_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ substr($session->expected_start_time, 0, 5) }} - {{ substr($session->expected_end_time, 0, 5) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colors = ['scheduled' => 'slate', 'open' => 'green', 'completed' => 'indigo', 'cancelled' => 'red'];
                            @endphp
                            <x-ui.badge :color="$colors[$session->status]">{{ $session->statusLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @if (in_array($session->status, ['scheduled', 'open', 'completed']))
                                    <a href="{{ route('tenant.sessions.manage', ['tenant' => $currentTenant->slug, 'session' => $session->id]) }}" class="rounded-lg border border-indigo-200 px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                        {{ $session->status === 'scheduled' ? 'بدء الحصة' : 'إدارة' }}
                                    </a>
                                @endif

                                @if ($session->status === 'scheduled')
                                    <button wire:click="edit({{ $session->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        تعديل
                                    </button>
                                    <button wire:click="openCancelForm({{ $session->id }})" class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                        إلغاء
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                            لا توجد حصص بعد. استخدم "توليد من الجدول" لإنشاء الحصص تلقائيًا حسب مواعيد المجموعة.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.modal :show="$showManualForm" :title="$editingId ? 'تعديل الحصة' : 'حصة يدوية جديدة'" on-close="$set('showManualForm', false)">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input name="scheduled_date" type="date" label="تاريخ الحصة" wire:model="scheduled_date" :error="$errors->first('scheduled_date')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="expected_start_time" type="time" label="وقت البداية" wire:model="expected_start_time" :error="$errors->first('expected_start_time')" />
                <x-ui.input name="expected_end_time" type="time" label="وقت النهاية" wire:model="expected_end_time" :error="$errors->first('expected_end_time')" />
            </div>

            <x-ui.input name="title" label="عنوان الحصة (اختياري)" wire:model="title" />
            <x-ui.input name="lesson_topic" label="موضوع الدرس (اختياري)" wire:model="lesson_topic" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">ملاحظات (اختياري)</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">حفظ</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showManualForm', false)">إلغاء</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal :show="$showCancelForm" title="إلغاء الحصة" on-close="$set('showCancelForm', false)">
        <form wire:submit="cancel" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">سبب الإلغاء</label>
                <textarea wire:model="cancellation_reason" rows="3" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="مثال: ظرف طارئ للمدرس"></textarea>
                @error('cancellation_reason') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <x-ui.button type="submit">تأكيد الإلغاء</x-ui.button>
                <x-ui.button type="button" variant="secondary" wire:click="$set('showCancelForm', false)">تراجع</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>

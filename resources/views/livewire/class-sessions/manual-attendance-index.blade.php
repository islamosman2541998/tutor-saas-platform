<div>
    <a href="{{ route('tenant.sessions.manage', ['tenant' => $currentTenant->slug, 'session' => $session->id]) }}" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">← إدارة الحصة</a>

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">تسجيل حضور اليوم</h1>
            <p class="text-sm text-slate-500">{{ $session->group->name }} — {{ $session->scheduled_date->format('Y-m-d') }}</p>
        </div>

        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="ابحث عن طالب..."
            class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <button wire:click="selectAllVisible" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50">
            تحديد الكل كحاضر
        </button>
        <button wire:click="clearAllVisible" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
            إلغاء التحديد
        </button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[640px] text-right text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">الطالب</th>
                    <th class="px-4 py-3 font-medium">طريقة التسجيل</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($enrollments as $enrollment)
                    @php $studentId = $enrollment->student_id; @endphp
                    <tr wire:key="att-{{ $studentId }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $enrollment->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $enrollment->student->student_code }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if (($methods[$studentId] ?? null) === 'qr')
                                <x-ui.badge color="indigo">QR</x-ui.badge>
                            @elseif ($methods[$studentId] ?? null)
                                <span class="text-xs text-slate-400">يدوي</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <select
                                wire:change="setStatus({{ $studentId }}, $event.target.value)"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            >
                                @foreach (['unmarked' => 'غير محدد', 'present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'بعذر'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($statuses[$studentId] ?? 'unmarked') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-slate-400">
                            لا يوجد طلاب مشتركون في هذه المجموعة.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-ui.button wire:click="askSave" class="w-auto px-6">حفظ الحضور</x-ui.button>
    </div>
</div>

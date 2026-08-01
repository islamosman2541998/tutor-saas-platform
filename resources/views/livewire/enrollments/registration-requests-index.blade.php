<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold">طلبات التسجيل</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">طلبات الانضمام اللي وصلت من الموقع التعريفي</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="ابحث بالاسم أو رقم الهاتف..."
                class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">كل الحالات</option>
                <option value="pending">قيد المراجعة</option>
                <option value="approved">مقبول</option>
                <option value="rejected">مرفوض</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[760px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">اسم الطالب</th>
                    <th class="px-4 py-3 font-medium">الهاتف</th>
                    <th class="px-4 py-3 font-medium">هاتف ولي الأمر</th>
                    <th class="px-4 py-3 font-medium">المجموعة</th>
                    <th class="px-4 py-3 font-medium">الحالة</th>
                    <th class="px-4 py-3 font-medium">تاريخ الطلب</th>
                    <th class="px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($requests as $request)
                    <tr wire:key="registration-request-{{ $request->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $request->student_name }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->phone }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->guardian_phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->group->name }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusBadge = match ($request->status) {
                                    'approved' => ['green', 'مقبول'],
                                    'rejected' => ['red', 'مرفوض'],
                                    default => ['amber', 'قيد المراجعة'],
                                };
                            @endphp
                            <x-ui.badge :color="$statusBadge[0]">{{ $statusBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $request->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @if ($request->status === 'pending')
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        wire:click="askApprove({{ $request->id }})"
                                        class="rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50"
                                    >
                                        قبول
                                    </button>
                                    <button
                                        wire:click="askReject({{ $request->id }})"
                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                    >
                                        رفض
                                    </button>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا يوجد طلبات تسجيل بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>

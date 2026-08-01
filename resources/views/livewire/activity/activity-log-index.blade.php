<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">سجل النشاط</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">كل العمليات الحساسة اللي حصلت في حسابك (مين عمل إيه وإمتى)</p>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="ابحث في وصف العملية..."
            class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
        <select wire:model.live="causerId" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">كل المستخدمين</option>
            @foreach ($causers as $causer)
                <option value="{{ $causer->id }}">{{ $causer->name }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="fromDate" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        <input type="date" wire:model.live="toDate" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <table class="w-full min-w-[760px] text-right text-sm">
            <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 font-medium">التاريخ</th>
                    <th class="px-4 py-3 font-medium">العملية</th>
                    <th class="px-4 py-3 font-medium">العنصر</th>
                    <th class="px-4 py-3 font-medium">بواسطة</th>
                    <th class="px-4 py-3 font-medium">تفاصيل</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($activities as $activity)
                    <tr wire:key="activity-{{ $activity->id }}">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $activity->description }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            @if ($activity->subjectLabel())
                                {{ $activity->subjectLabel() }} <span class="text-xs text-slate-400 dark:text-slate-500">#{{ $activity->subject_id }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $activity->causer?->name ?? 'النظام' }}</td>
                        <td class="px-4 py-3">
                            @if (! empty($activity->properties) && count($activity->properties) > 0)
                                <button wire:click="viewDetails({{ $activity->id }})" class="rounded-lg border border-slate-300 dark:border-slate-600 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    عرض
                                </button>
                            @else
                                <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 dark:text-slate-500">لا يوجد نشاط مسجَّل بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>

    <x-ui.modal :show="(bool) $viewingActivity" title="تفاصيل العملية" on-close="closeDetails">
        @if ($viewingActivity)
            <div class="mb-4 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                <p><span class="font-medium text-slate-900 dark:text-slate-100">العملية:</span> {{ $viewingActivity->description }}</p>
                <p><span class="font-medium text-slate-900 dark:text-slate-100">التاريخ:</span> {{ $viewingActivity->created_at->format('Y-m-d H:i') }}</p>
                <p><span class="font-medium text-slate-900 dark:text-slate-100">بواسطة:</span> {{ $viewingActivity->causer?->name ?? 'النظام' }}</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-right text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($viewingActivity->properties ?? [] as $key => $value)
                            <tr>
                                <td class="w-1/3 bg-slate-50 dark:bg-slate-900/40 px-3.5 py-2.5 font-medium text-slate-600 dark:text-slate-400">{{ $key }}</td>
                                <td class="px-3.5 py-2.5 text-slate-900 dark:text-slate-100">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <x-ui.button type="button" variant="secondary" wire:click="closeDetails">إغلاق</x-ui.button>
            </div>
        @endif
    </x-ui.modal>
</div>

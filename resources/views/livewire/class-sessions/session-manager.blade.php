<div @if ($session->status === 'open') wire:poll.5s @endif>
    <a href="{{ route('tenant.groups.sessions', ['tenant' => $currentTenant->slug, 'group' => $session->group_id]) }}" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">← حصص المجموعة</a>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">حصة {{ $session->scheduled_date->format('Y-m-d') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ substr($session->expected_start_time, 0, 5) }} - {{ substr($session->expected_end_time, 0, 5) }}</p>
        </div>
        @php
            $colors = ['scheduled' => 'slate', 'open' => 'green', 'completed' => 'indigo', 'cancelled' => 'red'];
        @endphp
        <x-ui.badge :color="$colors[$session->status]">{{ $session->statusLabel() }}</x-ui.badge>
    </div>

    @if ($session->status === 'scheduled')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-8 text-center">
            <p class="mb-4 text-slate-500 dark:text-slate-400">الحصة لم تبدأ بعد.</p>
            <x-ui.button wire:click="start" class="mx-auto w-auto px-6">بدء الحصة</x-ui.button>
        </div>
    @elseif ($session->status === 'open')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 text-center">
                @if ($attendanceUrl)
                    <div class="mx-auto mb-4 inline-block rounded-xl bg-white dark:bg-slate-800 p-4 shadow-sm">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(260)->generate($attendanceUrl) !!}
                    </div>

                    @if ($session->qr_is_active)
                        <p
                            x-data="{
                                expiresAt: new Date('{{ $session->qr_expires_at?->toIso8601String() }}').getTime(),
                                remaining: '',
                                tick() {
                                    const diff = Math.max(0, Math.floor((this.expiresAt - Date.now()) / 1000));
                                    const m = Math.floor(diff / 60).toString().padStart(2, '0');
                                    const s = (diff % 60).toString().padStart(2, '0');
                                    this.remaining = m + ':' + s;
                                }
                            }"
                            x-init="tick(); setInterval(() => tick(), 1000)"
                            class="mb-4 text-sm text-slate-500 dark:text-slate-400"
                        >
                            صالح لمدة <span x-text="remaining" class="font-mono font-bold text-indigo-600"></span>
                        </p>
                    @else
                        <p class="mb-4 text-sm text-red-600">تم إيقاف رمز الحضور.</p>
                    @endif

                    <div class="flex flex-wrap justify-center gap-2">
                        <button wire:click="regenerateQr" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            توليد رمز جديد
                        </button>
                        <button wire:click="extendQr" class="rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            تمديد 10 دقائق
                        </button>
                        <button wire:click="stopQr" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50">
                            إيقاف الرمز
                        </button>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap justify-center gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('tenant.sessions.attendance', ['tenant' => $currentTenant->slug, 'session' => $session->id]) }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        تسجيل حضور يدوي
                    </a>
                    <button wire:click="close" wire:confirm="هل أنت متأكد من إغلاق الحصة؟ لن تتمكن من فتحها مرة أخرى." class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        إغلاق الحصة
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <h2 class="mb-3 font-bold">الحضور المسجَّل ({{ $records->count() }})</h2>
                <ul class="space-y-2">
                    @forelse ($records as $record)
                        <li wire:key="record-{{ $record->id }}" class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-900/40 px-3 py-2 text-sm">
                            <span>{{ $record->student->name }}</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $record->checked_in_at?->format('H:i') }}</span>
                        </li>
                    @empty
                        <li class="text-center text-sm text-slate-400 dark:text-slate-500">لا يوجد حضور مسجَّل حتى الآن.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @elseif ($session->status === 'completed')
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
            <h2 class="mb-3 font-bold">الحضور ({{ $records->count() }})</h2>
            <ul class="space-y-2">
                @foreach ($records as $record)
                    <li wire:key="record-{{ $record->id }}" class="flex items-center justify-between rounded-lg bg-slate-50 dark:bg-slate-900/40 px-3 py-2 text-sm">
                        <span>{{ $record->student->name }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $record->checked_in_at?->format('H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-red-700">
            هذه الحصة ملغاة. {{ $session->cancellation_reason }}
        </div>
    @endif
</div>

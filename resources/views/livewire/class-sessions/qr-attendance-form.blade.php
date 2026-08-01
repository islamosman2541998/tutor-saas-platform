<div>
    @if (! $session)
        <div class="text-center">
            <h1 class="mb-2 text-lg font-bold text-red-600">رابط غير صالح</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">تأكد من مسح رمز QR الصحيح، أو اطلب من المدرس رمزًا جديدًا.</p>
        </div>
    @elseif ($submitted)
        <div class="text-center">
            @if ($succeeded)
                <div class="mb-3 text-4xl">✅</div>
                <h1 class="mb-2 text-lg font-bold text-emerald-600">تم تسجيل الحضور</h1>
            @else
                <div class="mb-3 text-4xl">⚠️</div>
                <h1 class="mb-2 text-lg font-bold text-red-600">تعذّر تسجيل الحضور</h1>
            @endif
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $resultMessage }}</p>
        </div>
    @else
        <div class="mb-6 text-center">
            @if ($session->tenant->teacher_image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($session->tenant->teacher_image) }}" class="mx-auto mb-3 h-14 w-14 rounded-full object-cover">
            @endif
            <h1 class="text-lg font-bold">{{ $session->tenant->teacher_name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $session->group->name }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500">
                {{ $session->group->subject->name }} — {{ $session->group->grade->name }}
            </p>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                {{ $session->scheduled_date->format('Y-m-d') }} — {{ substr($session->expected_start_time, 0, 5) }}
            </p>
        </div>

        <form wire:submit="submit" class="space-y-4">
            <x-ui.input name="student_code" label="كود الطالب" wire:model="student_code" :error="$errors->first('student_code')" autofocus />
            <x-ui.input name="last4_phone" label="آخر 4 أرقام من رقم الهاتف" wire:model="last4_phone" :error="$errors->first('last4_phone')" inputmode="numeric" maxlength="4" />

            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">تسجيل الحضور</span>
                <span wire:loading wire:target="submit">جارٍ التسجيل...</span>
            </x-ui.button>
        </form>
    @endif
</div>

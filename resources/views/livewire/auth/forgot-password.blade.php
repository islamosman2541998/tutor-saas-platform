<div>
    <h1 class="mb-1 text-xl font-bold login-title">نسيت كلمة المرور؟</h1>
    <p class="mb-6 text-sm login-text">أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين.</p>

    @if ($status)
        <div class="mb-4 rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ $status }}</div>
    @endif

    <form wire:submit="sendResetLink" class="space-y-4">
        <x-ui.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            wire:model="email"
            :error="$errors->first('email')"
            autofocus
        />

        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendResetLink">إرسال رابط الاستعادة</span>
            <span wire:loading wire:target="sendResetLink">جارٍ الإرسال...</span>
        </x-ui.button>
    </form>
</div>

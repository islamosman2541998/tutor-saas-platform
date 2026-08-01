<div>
    <h1 class="mb-1 text-xl font-bold login-title">تعيين كلمة مرور جديدة</h1>
    <p class="mb-6 text-sm login-text">اختر كلمة مرور قوية لحسابك.</p>

    <form wire:submit="resetPassword" class="space-y-4">
        <x-ui.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            wire:model="email"
            :error="$errors->first('email')"
            autofocus
        />

        <x-ui.input
            name="password"
            type="password"
            label="كلمة المرور الجديدة"
            wire:model="password"
            :error="$errors->first('password')"
            autocomplete="new-password"
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            label="تأكيد كلمة المرور"
            wire:model="password_confirmation"
            autocomplete="new-password"
        />

        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="resetPassword">تحديث كلمة المرور</span>
            <span wire:loading wire:target="resetPassword">جارٍ الحفظ...</span>
        </x-ui.button>
    </form>
</div>

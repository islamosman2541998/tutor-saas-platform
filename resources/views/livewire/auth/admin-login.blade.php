<div>
    <h1 class="mb-1 text-xl font-bold login-title">دخول Super Admin</h1>
    <p class="mb-6 text-sm login-text">لوحة تحكم المنصة</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('status') }}</div>
    @endif

    <form wire:submit="login" class="space-y-4">
        <x-ui.input
            name="email"
            type="email"
            label="البريد الإلكتروني"
            wire:model="email"
            :error="$errors->first('email')"
            autofocus
            autocomplete="username"
        />

        <x-ui.input
            name="password"
            type="password"
            label="كلمة المرور"
            wire:model="password"
            :error="$errors->first('password')"
            autocomplete="current-password"
        />

        <div class="flex items-center justify-between text-sm login-text">
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400 dark:border-slate-600">
                تذكرني
            </label>

            <a href="{{ route('admin.password.request') }}" class="hover:underline" style="color: var(--login-input-focus);">
                نسيت كلمة المرور؟
            </a>
        </div>

        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">دخول</span>
            <span wire:loading wire:target="login">جارٍ الدخول...</span>
        </x-ui.button>
    </form>
</div>

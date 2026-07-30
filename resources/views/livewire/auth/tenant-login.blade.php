@php
    $loginSettings = \App\Models\LoginPageSettings::query()->first() ?? \App\Models\LoginPageSettings::defaults();
@endphp

<div>
    <h1 class="mb-1 text-xl font-bold login-title">تسجيل الدخول</h1>
    <p class="mb-6 text-sm login-text">{{ $currentTenant->teacher_name ?? '' }}</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-700">{{ session('status') }}</div>
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

        @if ($loginSettings->remember_me_enabled || $loginSettings->forgot_password_enabled)
            <div class="flex items-center justify-between text-sm login-text">
                @if ($loginSettings->remember_me_enabled)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                        تذكرني
                    </label>
                @else
                    <span></span>
                @endif

                @if ($loginSettings->forgot_password_enabled)
                    <a href="{{ route('tenant.password.request', ['tenant' => $currentTenant->slug]) }}" class="hover:underline" style="color: var(--login-input-focus);">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>
        @endif

        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">دخول</span>
            <span wire:loading wire:target="login">جارٍ الدخول...</span>
        </x-ui.button>
    </form>
</div>

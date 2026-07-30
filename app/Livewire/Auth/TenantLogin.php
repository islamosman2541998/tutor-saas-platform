<?php

namespace App\Livewire\Auth;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class TenantLogin extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(TenantContext $context)
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $tenant = $context->get();
        $throttleKey = Str::lower($this->email).'|'.$tenant->id.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', "محاولات كثيرة جدًا. حاول مرة أخرى بعد {$seconds} ثانية.");

            return;
        }

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError('email', 'بيانات الدخول غير صحيحة.');

            return;
        }

        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();

        Auth::user()->forceFill(['last_login_at' => now()])->save();

        $this->redirectRoute('tenant.dashboard', ['tenant' => $tenant->slug], navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.tenant-login');
    }
}

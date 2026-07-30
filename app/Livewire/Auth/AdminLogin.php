<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class AdminLogin extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $throttleKey = Str::lower($this->email).'|admin|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', "محاولات كثيرة جدًا. حاول مرة أخرى بعد {$seconds} ثانية.");

            return;
        }

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'tenant_id' => null,
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

        $this->redirectRoute('admin.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.admin-login');
    }
}

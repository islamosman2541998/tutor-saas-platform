<?php

namespace App\Livewire\Auth;

use App\Support\Tenancy\TenantContext;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(TenantContext $context)
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $tenant = $context->get();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
                'tenant_id' => $tenant?->id,
            ],
            function ($user) use ($tenant) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', 'رابط إعادة التعيين غير صالح أو منتهي الصلاحية.');

            return;
        }

        $routeName = $tenant ? 'tenant.login' : 'admin.login';
        $routeParams = $tenant ? ['tenant' => $tenant->slug] : [];

        session()->flash('status', 'تم تغيير كلمة المرور بنجاح، يمكنك الآن تسجيل الدخول.');

        $this->redirectRoute($routeName, $routeParams, navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}

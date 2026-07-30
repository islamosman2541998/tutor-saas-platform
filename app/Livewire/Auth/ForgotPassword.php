<?php

namespace App\Livewire\Auth;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ForgotPassword extends Component
{
    public string $email = '';

    public string $status = '';

    /**
     * Credentials always include tenant_id (null on the central/admin login)
     * so the broker resolves the user scoped to the right tenant — the same
     * email string can legitimately belong to different people in different
     * tenants.
     */
    public function sendResetLink(TenantContext $context)
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [], ['email' => 'البريد الإلكتروني']);

        $tenant = $context->get();

        $result = Password::sendResetLink([
            'email' => $this->email,
            'tenant_id' => $tenant?->id,
        ]);

        if ($result === Password::RESET_LINK_SENT) {
            $this->status = 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.';

            return;
        }

        $this->addError('email', 'لم نتمكن من العثور على حساب بهذا البريد الإلكتروني.');
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}

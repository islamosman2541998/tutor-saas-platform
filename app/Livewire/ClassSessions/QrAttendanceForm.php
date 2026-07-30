<?php

namespace App\Livewire\ClassSessions;

use App\Actions\ClassSessions\RegisterQrAttendanceAction;
use App\Models\ClassSession;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

/**
 * Public, unauthenticated page reached by scanning a session's QR code.
 * No {tenant} slug in the URL — the token itself identifies the session
 * (and therefore the tenant), so tenant resolution happens in boot()
 * instead of route middleware. boot() runs on every request this
 * component handles (initial GET *and* every subsequent Livewire AJAX
 * call), which is what makes that safe — see ResolveTenantForLivewireRequest
 * for why relying on route-based middleware alone would not be.
 */
#[Layout('components.layouts.guest')]
class QrAttendanceForm extends Component
{
    public string $token = '';

    public string $student_code = '';

    public string $last4_phone = '';

    public bool $submitted = false;

    public bool $succeeded = false;

    public string $resultMessage = '';

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function boot(): void
    {
        if (! $this->token) {
            return;
        }

        $session = ClassSession::withoutGlobalScope(TenantScope::class)
            ->where('qr_token', $this->token)
            ->first();

        if ($session) {
            app(TenantContext::class)->set($session->tenant);
            app(PermissionRegistrar::class)->setPermissionsTeamId($session->tenant_id);
        }
    }

    public function submit(RegisterQrAttendanceAction $action): void
    {
        $this->validate([
            'student_code' => ['required', 'string', 'max:50'],
            'last4_phone' => ['required', 'digits:4'],
        ], [], [
            'student_code' => 'كود الطالب',
            'last4_phone' => 'آخر 4 أرقام من الهاتف',
        ]);

        $throttleKey = 'qr-attend:'.request()->ip().':'.$this->token;

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $this->submitted = true;
            $this->succeeded = false;
            $this->resultMessage = 'محاولات كثيرة جدًا. حاول مرة أخرى بعد قليل.';

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $result = $action->execute(
            $this->token,
            $this->student_code,
            $this->last4_phone,
            request()->ip(),
            (string) request()->userAgent(),
        );

        if ($result['success']) {
            RateLimiter::clear($throttleKey);
        }

        $this->submitted = true;
        $this->succeeded = $result['success'];
        $this->resultMessage = $result['message'];
    }

    public function render()
    {
        $session = ClassSession::withoutGlobalScope(TenantScope::class)
            ->with(['group.subject', 'group.grade', 'tenant'])
            ->where('qr_token', $this->token)
            ->first();

        return view('livewire.class-sessions.qr-attendance-form', [
            'session' => $session,
        ]);
    }
}

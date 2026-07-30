<?php

namespace App\Livewire\Auth;

use App\Actions\Tenancy\RegisterTenantAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    public string $teacher_name = '';

    public string $activity_name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(RegisterTenantAction $action)
    {
        $data = $this->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'activity_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'teacher_name' => 'اسم المدرس',
            'activity_name' => 'اسم النشاط أو السنتر',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور',
        ]);

        $tenant = $action->execute($data);

        Auth::login($tenant->owner);

        $this->redirectRoute('tenant.pending-review', ['tenant' => $tenant->slug], navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}

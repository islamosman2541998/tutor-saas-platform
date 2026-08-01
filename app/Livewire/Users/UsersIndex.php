<?php

namespace App\Livewire\Users;

use App\Actions\Users\CreateStaffUserAction;
use App\Actions\Users\SetStaffUserStatusAction;
use App\Actions\Users\UpdateStaffUserAction;
use App\Livewire\Concerns\Notifies;
use App\Models\User;
use App\Support\Permissions\TenantPermissions;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UsersIndex extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = '';

    public string $password = '';

    public function mount(): void
    {
        $this->authorize('users.manage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('users.manage');

        $this->reset(['editingId', 'name', 'email', 'phone', 'role', 'password']);
        $this->role = TenantPermissions::ASSISTANT;
        $this->showForm = true;
    }

    public function edit(int $userId): void
    {
        $this->authorize('users.manage');

        $user = $this->findStaffOrFail($userId);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) $user->phone;
        $this->role = (string) $user->getRoleNames()->first();
        $this->password = '';
        $this->showForm = true;
    }

    public function save(CreateStaffUserAction $createAction, UpdateStaffUserAction $updateAction): void
    {
        $this->authorize('users.manage');

        $tenantId = app(TenantContext::class)->id();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->where('tenant_id', $tenantId)->ignore($this->editingId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(TenantPermissions::assignableRoles())],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'role' => 'الدور',
            'password' => 'كلمة المرور',
        ]);

        if ($this->editingId) {
            $user = $this->findStaffOrFail($this->editingId);
            $updateAction->execute($user, $data);
            $this->toast('تم تحديث بيانات المستخدم.');
        } else {
            $createAction->execute($data);
            $this->toast('تمت إضافة المستخدم.');
        }

        $this->showForm = false;
    }

    public function askToggleStatus(int $userId): void
    {
        $this->authorize('users.manage');

        $user = $this->findStaffOrFail($userId);
        $newStatus = ! $user->is_active;

        $this->confirmThen(
            confirmEvent: 'user-status-confirmed',
            title: $newStatus ? 'تفعيل المستخدم؟' : 'إيقاف المستخدم؟',
            text: $newStatus
                ? "سيتمكن \"{$user->name}\" من تسجيل الدخول مرة أخرى."
                : "لن يتمكن \"{$user->name}\" من تسجيل الدخول بعد الإيقاف.",
            params: ['userId' => $userId, 'newStatus' => $newStatus],
        );
    }

    #[On('user-status-confirmed')]
    public function toggleStatus(int $userId, bool $newStatus, SetStaffUserStatusAction $action): void
    {
        $this->authorize('users.manage');

        $user = $this->findStaffOrFail($userId);
        $action->execute($user, $newStatus);
        $this->toast($newStatus ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.');
    }

    /**
     * User has no BelongsToTenant/TenantScope (Super Admin accounts carry a
     * null tenant_id, which the scope's "no tenant in context" branch would
     * mishandle) — every lookup here MUST filter by the current tenant
     * explicitly, or one tenant's staff could read/edit another tenant's
     * user by guessing an id. The tenant owner is excluded too: there must
     * always be exactly one, assigned only at registration (see
     * RegisterTenantAction), never editable from this screen.
     */
    protected function findStaffOrFail(int $userId): User
    {
        $tenant = app(TenantContext::class)->get();

        return User::query()
            ->where('tenant_id', $tenant?->id)
            ->when($tenant, fn (Builder $q) => $q->where('id', '!=', $tenant->owner_user_id))
            ->findOrFail($userId);
    }

    public function render()
    {
        $tenant = app(TenantContext::class)->get();

        $users = User::query()
            ->where('tenant_id', $tenant?->id)
            ->when($this->search, fn (Builder $q) => $q->where(function (Builder $q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn (Builder $q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->with('roles')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.users.users-index', [
            'users' => $users,
            'ownerUserId' => $tenant?->owner_user_id,
            'assignableRoles' => TenantPermissions::assignableRoles(),
        ]);
    }
}

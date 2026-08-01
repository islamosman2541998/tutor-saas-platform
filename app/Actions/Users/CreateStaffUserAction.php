<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\Activity\TenantActivity;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Creates a staff account (assistant/accountant/content_manager) under the
 * set exactly once, at tenant registration (see RegisterTenantAction).
 */
class CreateStaffUserAction
{
    public function __construct(protected TenantContext $context) {}

    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'tenant_id' => $this->context->id(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $user->assignRole($data['role']);

            TenantActivity::log('إضافة مستخدم', $user, ['name' => $user->name, 'role' => $data['role']]);

            return $user;
        });
    }
}

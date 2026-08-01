<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\DB;

class UpdateStaffUserAction
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                ...(! empty($data['password']) ? ['password' => $data['password']] : []),
            ]);

            if ($user->getRoleNames()->first() !== $data['role']) {
                $user->syncRoles([$data['role']]);
            }

            TenantActivity::log('تعديل بيانات مستخدم', $user, ['name' => $user->name, 'role' => $data['role']]);

            return $user->fresh();
        });
    }
}

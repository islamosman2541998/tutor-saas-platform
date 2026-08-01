<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\Activity\TenantActivity;

/**
 * Toggles is_active rather than deleting — TenantLogin/AdminLogin both
 * require is_active => true in the Auth::attempt() credentials, so this
 * alone already fully blocks the account from signing in.
 */
class SetStaffUserStatusAction
{
    public function execute(User $user, bool $isActive): User
    {
        $user->update(['is_active' => $isActive]);

        TenantActivity::log($isActive ? 'تفعيل مستخدم' : 'إيقاف مستخدم', $user, ['name' => $user->name]);

        return $user->fresh();
    }
}

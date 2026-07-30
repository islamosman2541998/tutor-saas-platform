<?php

namespace Database\Seeders;

use App\Support\Permissions\TenantPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Permission names are global (shared across all tenants); only roles and
 * role assignments are Teams-scoped by tenant_id. Run once — each tenant's
 * own roles are created later by CreateDefaultTenantRolesAction when the
 * tenant registers.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TenantPermissions::all() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}

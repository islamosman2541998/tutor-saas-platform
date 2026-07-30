<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Support\Permissions\TenantPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the 4 built-in roles (Teacher Owner, Assistant, Accountant,
 * Content Manager) for a newly registered tenant, each pre-loaded with its
 * default permission set. Roles are Teams-scoped by tenant_id, so every
 * tenant gets its own independent copies the owner can later customise.
 */
class CreateDefaultTenantRolesAction
{
    public function execute(Tenant $tenant): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        foreach (TenantPermissions::defaultRolePermissions() as $roleName => $permissionNames) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            $permissions = Permission::query()->whereIn('name', $permissionNames)->get();
            $role->syncPermissions($permissions);
        }
    }
}

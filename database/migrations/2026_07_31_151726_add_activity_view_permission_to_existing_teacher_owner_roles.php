<?php

use App\Models\Tenant;
use App\Support\Permissions\TenantPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission names are global but each tenant's roles are synced once, at
 * registration (CreateDefaultTenantRolesAction) — adding 'activity.view' to
 * TenantPermissions::all() only affects tenants registered from here on.
 * This backfills every tenant that already exists: creates the permission
 * row (PermissionSeeder does this for fresh installs) and grants it to
 * their teacher_owner role only, matching the "owner-only by default"
 * intent — assistant/accountant/content_manager are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Permission::query()->firstOrCreate([
            'name' => 'activity.view',
            'guard_name' => 'web',
        ]);

        $registrar = app(PermissionRegistrar::class);

        Tenant::query()->each(function (Tenant $tenant) use ($registrar) {
            $registrar->setPermissionsTeamId($tenant->id);

            $role = Role::query()->where([
                'name' => TenantPermissions::TEACHER_OWNER,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ])->first();

            $role?->givePermissionTo('activity.view');
        });

        $registrar->setPermissionsTeamId(null);
    }

    public function down(): void
    {
        $registrar = app(PermissionRegistrar::class);

        Tenant::query()->each(function (Tenant $tenant) use ($registrar) {
            $registrar->setPermissionsTeamId($tenant->id);

            $role = Role::query()->where([
                'name' => TenantPermissions::TEACHER_OWNER,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ])->first();

            $role?->revokePermissionTo('activity.view');
        });

        $registrar->setPermissionsTeamId(null);
    }
};

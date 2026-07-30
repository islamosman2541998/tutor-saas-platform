<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\TenantPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates a brand-new Tenant + its Teacher Owner user, atomically.
 *
 * Everything a fresh tenant needs to exist safely (default roles, the owner
 * account) happens inside one transaction — a half-created tenant (e.g. one
 * with no owner) must never be observable.
 */
class RegisterTenantAction
{
    public function __construct(
        protected CreateDefaultTenantRolesAction $createDefaultTenantRoles,
    ) {}

    public function execute(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::query()->create([
                'name' => $data['activity_name'],
                'slug' => $this->uniqueSlug($data['activity_name']),
                'teacher_name' => $data['teacher_name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'status' => $data['status'] ?? 'pending',
                'website_status' => 'draft',
            ]);

            $this->createDefaultTenantRoles->execute($tenant);

            $owner = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['teacher_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
            $owner->assignRole(TenantPermissions::TEACHER_OWNER);

            $tenant->forceFill(['owner_user_id' => $owner->id])->save();

            return $tenant;
        });
    }

    protected function uniqueSlug(string $activityName): string
    {
        $base = Str::slug($activityName) ?: 'teacher';
        $slug = $base;
        $suffix = 1;

        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

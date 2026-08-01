<?php

namespace App\QueryObjects\Reports;

use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * User isn't tenant-scoped via BelongsToTenant/TenantScope (Super Admin
 * accounts carry a null tenant_id), so the current tenant is filtered
 * explicitly here rather than relying on a global scope.
 */
class UsersReportQuery
{
    /**
     * @param  array{status?: string, search?: string, role?: string}  $filters
     */
    public static function build(array $filters = []): Builder
    {
        return User::query()
            ->where('tenant_id', app(TenantContext::class)->id())
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('is_active', $status === 'active'))
            ->when($filters['role'] ?? null, fn (Builder $q, $role) => $q->whereHas('roles', fn (Builder $q) => $q->where('name', $role)))
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }
}

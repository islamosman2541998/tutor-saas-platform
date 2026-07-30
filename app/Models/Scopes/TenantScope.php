<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filters every query on a tenant-scoped model to the current tenant.
 *
 * Behaviour depends on TenantContext state:
 * - Not resolved yet (console/queue/seeders outside the web tenant pipeline):
 *   no filter is applied — the caller is responsible for scoping explicitly.
 * - Resolved with a tenant (normal tenant request, or Super Admin impersonating
 *   via Support Login): filter to that tenant_id.
 * - Resolved without a tenant (Super Admin browsing outside impersonation):
 *   the query is forced to return zero rows. Super Admin has no business
 *   reading tenant-scoped tables directly — it manages tenants/plans/subscriptions
 *   instead, which are not tenant-scoped models.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if (! $context->isResolved()) {
            return;
        }

        if ($context->hasTenant()) {
            $builder->where($model->qualifyColumn('tenant_id'), $context->id());

            return;
        }

        $builder->whereRaw('1 = 0');
    }
}

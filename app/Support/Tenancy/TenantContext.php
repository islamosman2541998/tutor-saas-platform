<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

/**
 * Holds the tenant resolved for the current request. Bound as a singleton so
 * TenantScope, Livewire components and Actions can all read the same instance
 * without re-resolving it from the request.
 */
class TenantContext
{
    protected ?Tenant $tenant = null;

    protected bool $resolved = false;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->resolved = true;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Whether ResolveTenant middleware has already run for this request.
     * Lets TenantScope distinguish "resolved to null" (Super Admin context)
     * from "not resolved yet" (e.g. console/queue context).
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }
}

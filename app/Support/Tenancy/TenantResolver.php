<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

/**
 * Every place that needs to figure out "which tenant is this request for"
 * goes through here — ResolveTenant (page loads) and
 * ResolveTenantForLivewireRequest (Livewire's /livewire/update AJAX
 * endpoint, which bypasses normal route middleware — see that class for why)
 * both call the same slug/host parsing instead of duplicating it.
 */
class TenantResolver
{
    public function fromSlug(?string $slug): ?Tenant
    {
        if (! $slug) {
            return null;
        }

        return Tenant::query()->where('slug', $slug)->first();
    }

    public function fromHost(string $host): ?Tenant
    {
        $central = config('tenancy.central_domain');

        if (! $central || ! str_ends_with($host, ".{$central}")) {
            return null;
        }

        $subdomain = str_replace(".{$central}", '', $host);

        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return null;
        }

        return $this->fromSlug($subdomain);
    }

    /**
     * Parses a request path like "teacher/ahmed-math/dashboard" — used to
     * recover tenant context from Livewire's snapshot memo, which carries
     * the path of the page the component was originally rendered on.
     */
    public function fromPath(string $path): ?Tenant
    {
        $path = ltrim($path, '/');

        if (preg_match('#^teacher/([^/]+)#', $path, $matches)) {
            return $this->fromSlug($matches[1]);
        }

        return null;
    }
}

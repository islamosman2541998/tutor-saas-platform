<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Livewire's component-update AJAX calls all hit a single fixed endpoint
 * (/livewire/update) that never passes through the per-route 'tenant' /
 * 'central' middleware — Livewire registers that route itself, outside our
 * route files. Without this, TenantContext would simply never be set for
 * any Livewire action (button clicks, form submits, ...), and TenantScope's
 * safe-default would silently return zero rows for every tenant-scoped
 * query triggered from a Livewire component.
 *
 * Fix: every update request carries the originating page's snapshot, which
 * includes the path it was rendered on (e.g. "teacher/ahmed-math/login").
 * We recover the tenant from that path the same way ResolveTenant recovers
 * it from the live route, and set the exact same context.
 */
class ResolveTenantForLivewireRequest
{
    public function __construct(
        protected TenantContext $context,
        protected TenantResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->fromPath($this->extractPath($request));

        $this->context->set($tenant);

        if ($tenant) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
            // Mirrors ResolveTenant's view()->share() so Blade views that
            // reference $currentTenant render identically whether they came
            // from the initial page load or a subsequent Livewire update.
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }

    protected function extractPath(Request $request): string
    {
        $components = $request->input('components', []);

        if (! is_array($components) || empty($components)) {
            return '';
        }

        $snapshot = json_decode($components[0]['snapshot'] ?? '', true);

        return $snapshot['memo']['path'] ?? '';
    }
}

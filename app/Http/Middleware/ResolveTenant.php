<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the Tenant for the current request and binds it into TenantContext
 * before any tenant-scoped query can run.
 *
 * Resolution order:
 * 1. Route parameter {tenant} (path-based, e.g. /teacher/{tenant}/... ) — used
 *    in local development where wildcard subdomains are not configured.
 * 2. Subdomain of CENTRAL_DOMAIN (e.g. ahmed-math.tutor-saas.test) — used in
 *    production and once wildcard *.CENTRAL_DOMAIN is set up locally.
 *
 * If a tenant is resolved but the currently authenticated user belongs to a
 * *different* tenant, the session is dropped immediately: a user must never
 * remain authenticated while looking at another tenant's slug, even if the
 * mismatch only happened because they edited the URL by hand.
 *
 * Note: this middleware only covers normal page loads. Livewire's
 * /livewire/update endpoint bypasses per-route middleware entirely — see
 * ResolveTenantForLivewireRequest for how that request re-derives the same
 * TenantContext from the component's snapshot instead.
 */
class ResolveTenant
{
    public function __construct(
        protected TenantContext $context,
        protected TenantResolver $resolver,
        protected AuthManager $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveFromRoute($request) ?? $this->resolver->fromHost($request->getHost());

        abort_if($tenant === null, 404);

        $this->context->set($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        $user = $this->auth->guard('web')->user();

        if ($user && (int) $user->tenant_id !== $tenant->id) {
            $this->auth->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tenant.login', ['tenant' => $tenant->slug])
                ->withErrors(['email' => 'من فضلك سجّل الدخول من الحساب الصحيح لهذا الموقع.']);
        }

        view()->share('currentTenant', $tenant);

        return $next($request);
    }

    protected function resolveFromRoute(Request $request): ?Tenant
    {
        $route = $request->route();

        if (! $route || ! $route->hasParameter('tenant')) {
            return null;
        }

        $value = $route->parameter('tenant');

        if ($value instanceof Tenant) {
            return $value;
        }

        return $this->resolver->fromSlug($value);
    }
}

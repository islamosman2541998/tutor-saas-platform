<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every tenant route on subscription status, separately from
 * tenant.status (see EnsureTenantIsActive) — a tenant can be perfectly
 * "active" as an account while its subscription has lapsed. Must run after
 * ResolveTenant and EnsureTenantIsActive.
 *
 * No subscription row at all is treated the same as an expired one: every
 * tenant is expected to get one via AssignTenantSubscriptionAction (at
 * approval time or later), so a missing row means access was never granted
 * rather than something to fail open on.
 */
class EnsureSubscriptionIsValid
{
    protected const ALLOWED_WHILE_EXPIRED = ['tenant.subscription-expired', 'tenant.logout'];

    public function __construct(protected TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->get();

        if (! $tenant) {
            return $next($request);
        }

        $subscription = $tenant->currentSubscription();

        if ($subscription && $subscription->isCurrentlyValid()) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWED_WHILE_EXPIRED, true)) {
            return $next($request);
        }

        return redirect()->route('tenant.subscription-expired', ['tenant' => $tenant->slug]);
    }
}

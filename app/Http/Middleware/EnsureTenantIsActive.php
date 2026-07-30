<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every tenant route on the tenant.status flag. Must run after
 * ResolveTenant.
 *
 * - active: passes through untouched.
 * - pending (awaiting Super Admin review): redirected to the
 *   pending-review notice for every route except that notice itself and
 *   logout — a newly-registered teacher must see "under review", never
 *   the dashboard.
 * - suspended / rejected: hard-blocked with a 403.
 *
 * Subscription-expiry handling (EnsureSubscriptionIsValid /
 * EnsurePlanAllowsFeature) is added in the Plans & Subscriptions step — this
 * middleware only covers the tenant.status flag.
 */
class EnsureTenantIsActive
{
    protected const ALLOWED_WHILE_PENDING = ['tenant.pending-review', 'tenant.logout'];

    public function __construct(protected TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->get();

        if (! $tenant || $tenant->isActive()) {
            return $next($request);
        }

        if ($tenant->isPending()) {
            if (in_array($request->route()?->getName(), self::ALLOWED_WHILE_PENDING, true)) {
                return $next($request);
            }

            return redirect()->route('tenant.pending-review', ['tenant' => $tenant->slug]);
        }

        abort(403, $tenant->isRejected()
            ? 'تم رفض طلب التسجيل الخاص بهذا الحساب. تواصل مع الدعم الفني للمنصة.'
            : 'تم إيقاف هذا الحساب مؤقتًا. تواصل مع الدعم الفني للمنصة.');
    }
}

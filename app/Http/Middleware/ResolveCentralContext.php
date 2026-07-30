<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks the request as explicitly tenant-less (Super Admin / marketing /
 * central routes). Without this, TenantContext would stay "unresolved" on
 * these routes and TenantScope would skip filtering entirely — this
 * middleware makes sure the scope's safe default (return zero rows) applies
 * instead if a tenant-scoped model is ever queried by mistake outside a
 * tenant request.
 */
class ResolveCentralContext
{
    public function __construct(protected TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->set(null);

        return $next($request);
    }
}

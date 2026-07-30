<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a tenant user's session from reaching /admin/* routes. 'auth' alone
 * only proves *someone* is logged in — since the session cookie is shared
 * across the whole .CENTRAL_DOMAIN, a tenant owner's own session would
 * otherwise pass straight through the central 'auth' group too.
 */
class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'هذه الصفحة مخصصة لإدارة المنصة فقط.');

        return $next($request);
    }
}

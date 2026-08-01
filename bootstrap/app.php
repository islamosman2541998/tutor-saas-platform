<?php

use App\Http\Middleware\EnsureIsSuperAdmin;
use App\Http\Middleware\EnsureSubscriptionIsValid;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\ResolveCentralContext;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'central' => ResolveCentralContext::class,
            'tenant.active' => EnsureTenantIsActive::class,
            'tenant.subscription' => EnsureSubscriptionIsValid::class,
            'super_admin' => EnsureIsSuperAdmin::class,
        ]);

        // Laravel's 'web' group bakes SubstituteBindings in near the end of
        // the group, which runs BEFORE any of our route-specific middleware
        // (including 'tenant'). Left alone, implicit route-model-binding on
        // a tenant-scoped model (e.g. GradesIndex::mount(Stage $stage))
        // would resolve with TenantContext still unset, and TenantScope's
        // "not resolved yet" branch skips filtering entirely — silently
        // letting one tenant bind another tenant's model by guessing an id.
        // Forcing our tenant-resolution middleware to run first closes that.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: ResolveTenant::class,
        );
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: ResolveCentralContext::class,
        );

        // No single "login" route exists — every tenant (and the central
        // admin panel) has its own. Redirect guests to whichever one
        // matches the slug they were trying to reach.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($slug = $request->route('tenant')) {
                return route('tenant.login', ['tenant' => $slug]);
            }

            return route('admin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // api/* always gets JSON regardless of headers (genuine API
        // consumers). Everything else falls back to Laravel's normal
        // expectsJson() check — needed for plain fetch()-based endpoints
        // like the public registration-request form, which live under the
        // tenant path, not api/*, but still need JSON validation errors
        // instead of a redirect-back.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

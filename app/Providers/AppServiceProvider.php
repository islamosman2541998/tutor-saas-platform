<?php

namespace App\Providers;

use App\Http\Middleware\ResolveTenantForLivewireRequest;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Replaces Livewire's default /livewire/update route so it also runs
        // through tenant resolution — see ResolveTenantForLivewireRequest.
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', ResolveTenantForLivewireRequest::class]);
        });

        ResetPassword::createUrlUsing(function (User $notifiable, string $token) {
            $tenant = $notifiable->tenant;

            return url(route(
                $tenant ? 'tenant.password.reset' : 'admin.password.reset',
                array_filter([
                    'tenant' => $tenant?->slug,
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]),
                false
            ));
        });
    }
}

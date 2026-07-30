<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Admin\PlansIndex;
use App\Livewire\Admin\SubscriptionsIndex;
use App\Livewire\Admin\TenantCreate;
use App\Livewire\Admin\TenantsIndex;
use App\Livewire\Auth\AdminLogin;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\ClassSessions\QrAttendanceForm;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

// رابط مسح الطالب العام: لا {tenant} في الرابط — الـtoken نفسه يحدد
// الحصة والـTenant (يتم داخل QrAttendanceForm::boot()، راجع تعليقاتها).
// throttle:20,1 حماية أساسية إضافية فوق الحد الداخلي في الـAction نفسه.
Route::get('/attend/{token}', QrAttendanceForm::class)
    ->middleware('throttle:20,1')
    ->name('attend');

// المنصة المركزية: الصفحة التسويقية، تسجيل مدرس جديد، ودخول Super Admin.
Route::middleware('central')->group(function () {
    Route::get('/', function () {
        return view('marketing.home');
    })->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('/register', Register::class)->name('register');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/login', AdminLogin::class)->name('login');
            Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
            Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
        Route::post('/logout', LogoutController::class)->name('logout');

        Route::middleware('super_admin')->group(function () {
            Route::get('/dashboard', function () {
                return view('admin.dashboard', [
                    'tenantsCount' => Tenant::query()->count(),
                    'activeTenantsCount' => Tenant::query()->where('status', 'active')->count(),
                    'pendingTenantsCount' => Tenant::query()->where('status', 'pending')->count(),
                    'plansCount' => Plan::query()->where('is_active', true)->count(),
                ]);
            })->name('dashboard');

            Route::get('/tenants', TenantsIndex::class)->name('tenants');
            Route::get('/tenants/create', TenantCreate::class)->name('tenants.create');
            Route::get('/plans', PlansIndex::class)->name('plans');
            Route::get('/subscriptions', SubscriptionsIndex::class)->name('subscriptions');
        });
    });
});

require __DIR__.'/tenant.php';

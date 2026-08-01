<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Billing\PaymentReceiptController;
use App\Http\Controllers\Reports\ReportDownloadController;
use App\Http\Controllers\Tenancy\PendingReviewController;
use App\Http\Controllers\Tenancy\SubscriptionExpiredController;
use App\Http\Controllers\Website\PublicPageController;
use App\Http\Controllers\Website\PublicPostController;
use App\Http\Controllers\Website\PublicTipsIndexController;
use App\Http\Controllers\Website\PublicWebsiteController;
use App\Http\Controllers\Website\SubmitRegistrationRequestController;
use App\Livewire\AcademicStructure\GradesIndex;
use App\Livewire\AcademicStructure\LocationsIndex;
use App\Livewire\AcademicStructure\StagesIndex;
use App\Livewire\AcademicStructure\SubjectsIndex;
use App\Livewire\AcademicYears\AcademicYearsIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\TenantLogin;
use App\Livewire\Billing\MonthlyDuesIndex;
use App\Livewire\Billing\OverdueDuesIndex;
use App\Livewire\Billing\PaymentsIndex;
use App\Livewire\ClassSessions\ClassSessionsIndex;
use App\Livewire\ClassSessions\ManualAttendanceIndex;
use App\Livewire\ClassSessions\SessionManager;
use App\Livewire\Dashboard\TenantDashboardIndex;
use App\Livewire\Enrollments\GroupEnrollmentsIndex;
use App\Livewire\Enrollments\RegistrationRequestsIndex;
use App\Livewire\Groups\GroupSchedulesIndex;
use App\Livewire\Groups\GroupsIndex;
use App\Livewire\Reports\ReportsIndex;
use App\Livewire\Settings\DashboardAppearanceForm;
use App\Livewire\Settings\LoginPageAppearanceForm;
use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Students\StudentProfile;
use App\Livewire\Students\StudentsIndex;
use App\Livewire\Users\UsersIndex;
use App\Livewire\Website\NavbarItemsIndex;
use App\Livewire\Website\PagesIndex;
use App\Livewire\Website\PostsIndex;
use App\Livewire\Website\SlidersIndex;
use App\Livewire\Website\SocialLinksIndex;
use App\Livewire\Website\TestimonialsIndex;
use App\Livewire\Website\WebsiteSectionsIndex;
use App\Livewire\Website\WebsiteSettingsForm;
use Illuminate\Support\Facades\Route;

/*
 * Path-based tenant routes for local development: /teacher/{tenant}/...
 * In production these resolve identically from the tenant's subdomain
 * (ahmed-math.tutor-saas.test) — ResolveTenant handles both, see its
 * resolveFromRoute()/resolveFromSubdomain() methods.
 */
Route::prefix('teacher/{tenant}')
    ->middleware(['tenant', 'tenant.active', 'tenant.subscription'])
    ->name('tenant.')
    ->group(function () {
        // Public marketing homepage — no auth, no guest requirement; the
        // draft/maintenance gate lives inside the controller itself so the
        // tenant's own logged-in staff can still preview an unpublished
        // site at the same URL everyone else uses.
        Route::get('/', PublicWebsiteController::class)->name('website.home');
        Route::get('/tips', PublicTipsIndexController::class)->name('website.tips.index');
        Route::get('/tips/{post}', PublicPostController::class)->name('website.tips.show');
        Route::get('/pages/{page}', PublicPageController::class)->name('website.pages.show');

        // Anonymous lead-capture from the public groups section — throttled
        // since it has no auth/guest gate at all.
        Route::post('/register-request', SubmitRegistrationRequestController::class)
            ->middleware('throttle:10,1')
            ->name('website.register-request');

        // Shown to a newly-registered teacher (and to anyone hitting a
        // route while their tenant is still pending) instead of the
        // dashboard — see EnsureTenantIsActive.
        Route::get('/pending-review', PendingReviewController::class)->name('pending-review');

        // Shown instead of the dashboard once the tenant's subscription has
        // lapsed — see EnsureSubscriptionIsValid.
        Route::get('/subscription-expired', SubscriptionExpiredController::class)->name('subscription-expired');

        Route::middleware('guest')->group(function () {
            Route::get('/login', TenantLogin::class)->name('login');
            Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
            Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
        });

        Route::middleware('auth')->group(function () {
            Route::get('/dashboard', TenantDashboardIndex::class)->name('dashboard');

            Route::get('/academic-years', AcademicYearsIndex::class)->name('academic-years');

            Route::get('/stages', StagesIndex::class)->name('stages');
            Route::get('/stages/{stage}/grades', GradesIndex::class)->name('stages.grades');

            Route::get('/subjects', SubjectsIndex::class)->name('subjects');

            Route::get('/locations', LocationsIndex::class)->name('locations');

            Route::get('/groups', GroupsIndex::class)->name('groups');
            Route::get('/registration-requests', RegistrationRequestsIndex::class)->name('registration-requests');
            Route::get('/groups/{group}/schedules', GroupSchedulesIndex::class)->name('groups.schedules');
            Route::get('/groups/{group}/students', GroupEnrollmentsIndex::class)->name('groups.students');
            Route::get('/groups/{group}/sessions', ClassSessionsIndex::class)->name('groups.sessions');
            Route::get('/sessions/{session}', SessionManager::class)->name('sessions.manage');
            Route::get('/sessions/{session}/attendance', ManualAttendanceIndex::class)->name('sessions.attendance');

            Route::get('/students', StudentsIndex::class)->name('students');
            Route::get('/students/{student}', StudentProfile::class)->name('students.show');

            Route::get('/dues', MonthlyDuesIndex::class)->name('dues');

            Route::get('/payments', PaymentsIndex::class)->name('payments');
            Route::get('/payments/{payment}/receipt', PaymentReceiptController::class)->name('payments.receipt');

            Route::get('/overdue', OverdueDuesIndex::class)->name('overdue');

            Route::get('/reports', ReportsIndex::class)->name('reports');
            Route::get('/reports/download/{filename}', ReportDownloadController::class)
                ->middleware('signed')->name('reports.download');

            Route::get('/website-settings', WebsiteSettingsForm::class)->name('website-settings');
            Route::get('/website-navbar', NavbarItemsIndex::class)->name('website.navbar');
            Route::get('/website-social-links', SocialLinksIndex::class)->name('website.social-links');
            Route::get('/website-sections', WebsiteSectionsIndex::class)->name('website.sections');
            Route::get('/website-sliders', SlidersIndex::class)->name('website.sliders');
            Route::get('/website-posts', PostsIndex::class)->name('website.posts');
            Route::get('/website-testimonials', TestimonialsIndex::class)->name('website.testimonials');
            Route::get('/website-pages', PagesIndex::class)->name('website.pages');

            Route::get('/appearance/dashboard', DashboardAppearanceForm::class)->name('appearance.dashboard');
            Route::get('/appearance/login', LoginPageAppearanceForm::class)->name('appearance.login');

            Route::get('/users', UsersIndex::class)->name('users');
            Route::get('/activity-log', ActivityLogIndex::class)->name('activity-log');

            Route::post('/logout', LogoutController::class)->name('logout');
        });
    });

<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\NavbarItem;
use App\Models\Post;
use App\Models\Slider;
use App\Models\SocialLink;
use App\Models\Stage;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Testimonial;
use App\Models\WebsiteSection;
use App\Models\WebsiteSettings;
use App\Support\Tenancy\TenantContext;

class PublicWebsiteController extends Controller
{
    public function __invoke(TenantContext $context)
    {
        $tenant = $context->get();
        $settings = WebsiteSettings::query()->first() ?? new WebsiteSettings;

        $isOwnPreview = auth()->check() && auth()->user()->tenant_id === $tenant->id;

        if ($tenant->website_status !== 'published' && ! $isOwnPreview) {
            return response()->view('website.unavailable', [
                'tenant' => $tenant,
                'settings' => $settings,
            ], $tenant->website_status === 'maintenance' ? 503 : 404);
        }

        // Also synced here (not just from WebsiteSectionsIndex::mount())
        // so a tenant who publishes without ever opening the sections
        // management page still gets a real homepage instead of an empty
        // one — syncDefaults() is idempotent, so this is a no-op past the
        // first visit.
        WebsiteSection::syncDefaults();

        // Only sections that are visible AND already have a real content
        // source (see WebsiteSection::DEFAULTS) render on the live page —
        // join_form stays manageable in the dashboard ahead of its own
        // Website Builder sub-step, without showing an empty section
        // publicly in the meantime.
        $sections = WebsiteSection::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (WebsiteSection $section) => $section->isReady());

        return view('website.home', [
            'tenant' => $tenant,
            'settings' => $settings,
            'isOwnPreview' => $isOwnPreview,
            'navbarItems' => NavbarItem::query()->where('is_visible', true)->orderBy('sort_order')->get(),
            'navSocialLinks' => SocialLink::query()->visibleIn('navbar')->orderBy('sort_order')->get(),
            'footerSocialLinks' => SocialLink::query()->visibleIn('footer')->orderBy('sort_order')->get(),
            'contactSocialLinks' => SocialLink::query()->visibleIn('contact')->orderBy('sort_order')->get(),
            'sections' => $sections,
            'sliders' => Slider::query()->currentlyRunning()->orderBy('sort_order')->get(),
            'stats' => [
                'students' => Student::query()->where('status', 'active')->count(),
                'groups' => Group::query()->where('status', 'active')->count(),
                'subjects' => Subject::query()->where('is_active', true)->count(),
                'years' => $tenant->years_of_experience,
            ],
            'publicSubjects' => Subject::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'publicStages' => Stage::query()->where('is_active', true)->with(['grades' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->orderBy('sort_order')->get(),
            'publicGroups' => Group::query()->where('status', 'active')->where('allow_online_registration', true)
                ->with(['subject', 'grade'])->orderBy('name')->get(),
            'tips' => Post::query()->publicly()
                ->orderByDesc('is_featured')->orderByDesc('published_at')
                ->limit(6)->get(),
            'testimonials' => Testimonial::query()->publiclyVisible()
                ->orderByDesc('is_featured')->orderBy('sort_order')
                ->limit(9)->get(),
        ]);
    }
}

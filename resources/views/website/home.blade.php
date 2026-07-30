<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->site_name ?: $tenant->teacher_name }}</title>
    @if ($settings->short_description)
        <meta name="description" content="{{ $settings->short_description }}">
    @endif
    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/website.js'])
    <x-website.head-styles :settings="$settings" />
</head>
<body class="min-h-screen antialiased">
    @if ($isOwnPreview && $tenant->website_status !== 'published')
        <div class="bg-amber-500 px-4 py-2 text-center text-sm font-medium text-white">
            وضع المعاينة — هذا الموقع غير منشور بعد ولا يظهر للزوار.
        </div>
    @endif

    <x-website.header :settings="$settings" :tenant="$tenant" :navbar-items="$navbarItems" :nav-social-links="$navSocialLinks" />

    <main>
        @forelse ($sections as $section)
            @switch ($section->section_key)
                @case ('hero_slider')
                    @if ($sliders->isNotEmpty())
                        <section data-hero-slider class="site-hero relative overflow-hidden">
                            <div class="absolute inset-0 overflow-hidden">
                                <div data-slider-track class="site-hero-track flex h-full">
                                    @foreach ($sliders as $slide)
                                        <div data-slider-slide class="site-hero-slide relative h-full shrink-0">
                                            <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}" class="absolute inset-0 h-full w-full object-cover">
                                            <div class="absolute inset-0" style="background: linear-gradient(0deg, rgba(15,23,42,.75), rgba(15,23,42,.35) 55%, rgba(15,23,42,.15));"></div>

                                            @if ($slide->title || $slide->description || ($slide->button_text && $slide->button_url))
                                                <div class="relative z-10 flex h-full items-center justify-end px-6 sm:px-16">
                                                    <div class="max-w-xl text-center text-white sm:text-right" dir="rtl">
                                                        @if ($slide->title)
                                                            <h1 class="text-3xl font-bold leading-tight sm:text-5xl" style="font-size: var(--site-heading-size);">{{ $slide->title }}</h1>
                                                        @endif
                                                        @if ($slide->description)
                                                            <p class="mx-auto mt-4 max-w-lg text-base leading-8 opacity-90 sm:mx-0">{{ $slide->description }}</p>
                                                        @endif
                                                        @if ($slide->button_text && $slide->button_url)
                                                            <a href="{{ $slide->button_url }}" @if($slide->open_in_new_tab) target="_blank" rel="noopener" @endif class="site-btn mt-7 inline-block px-6 py-3 text-sm font-bold text-white">{{ $slide->button_text }}</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if ($sliders->count() > 1)
                                <button type="button" data-slider-prev aria-label="السابق" class="site-slider-arrow site-slider-arrow-prev">
                                    <x-ui.icon name="arrow-left" class="h-4 w-4" />
                                </button>
                                <button type="button" data-slider-next aria-label="التالي" class="site-slider-arrow site-slider-arrow-next">
                                    <x-ui.icon name="arrow-left" class="h-4 w-4 rotate-180" />
                                </button>

                                <div class="absolute inset-x-0 bottom-6 z-10 flex justify-center gap-2">
                                    @foreach ($sliders as $i => $slide)
                                        <button type="button" data-slider-dot data-index="{{ $i }}" aria-label="سلايد {{ $i + 1 }}" class="site-slider-dot"></button>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @else
                        <section class="relative overflow-hidden px-4 py-16 sm:py-24" style="background: linear-gradient(160deg, color-mix(in srgb, var(--site-primary) 10%, transparent), transparent 55%);">
                            <div class="site-float absolute -right-10 -top-10 h-56 w-56 rounded-full opacity-30 blur-3xl" style="background: var(--site-primary);"></div>
                            <div class="site-float-slow absolute -left-16 bottom-0 h-64 w-64 rounded-full opacity-20 blur-3xl" style="background: var(--site-secondary);"></div>

                            <div class="relative mx-auto max-w-2xl py-6 text-center">
                                <div data-reveal="fade" class="mx-auto mb-7 flex h-32 w-32 items-center justify-center rounded-full text-4xl font-bold text-white shadow-xl site-pulse-ring" style="background: linear-gradient(135deg, var(--site-primary), var(--site-secondary));">
                                    @if ($settings->teacherImageUrl())
                                        <img src="{{ $settings->teacherImageUrl() }}" alt="{{ $tenant->teacher_name }}" class="h-full w-full rounded-full object-cover">
                                    @else
                                        {{ mb_substr($tenant->teacher_name, 0, 1) }}
                                    @endif
                                </div>

                                <p data-reveal data-reveal-delay="1" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-semibold" style="background: color-mix(in srgb, var(--site-primary) 12%, transparent); color: var(--site-primary);">
                                    <x-ui.icon name="sparkle" class="h-3.5 w-3.5" /> منصة تعليمية خاصة
                                </p>

                                <h1 data-reveal data-reveal-delay="2" class="mt-5 text-4xl font-bold leading-tight sm:text-5xl" style="font-size: var(--site-heading-size);">
                                    {{ $tenant->teacher_name }}
                                </h1>

                                @if ($settings->short_description)
                                    <p data-reveal data-reveal-delay="3" class="mx-auto mt-5 max-w-xl text-base leading-8 opacity-80">{{ $settings->short_description }}</p>
                                @endif

                                <div data-reveal data-reveal-delay="4" class="mt-8 flex flex-wrap items-center justify-center gap-3">
                                    <a href="#groups" class="site-btn px-6 py-3 text-sm font-bold text-white">سجّل الآن</a>
                                    <a href="#about" class="site-btn-outline px-6 py-3 text-sm font-bold">تعرّف أكثر</a>
                                </div>
                            </div>
                        </section>
                    @endif
                    @break

                @case ('about')
                    <section id="about" data-reveal class="mx-auto max-w-3xl px-4 py-16 text-center">
                        <span class="text-xs font-bold uppercase tracking-widest" style="color: var(--site-primary);">من نحن</span>
                        <h2 class="mt-2 text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>
                        @if ($section->subtitle)<p class="mt-1 text-sm opacity-60">{{ $section->subtitle }}</p>@endif

                        <p class="mx-auto mt-5 max-w-xl text-base leading-8 opacity-80">
                            {{ $settings->short_description ?: $tenant->bio }}
                        </p>

                        @if ($tenant->years_of_experience)
                            <div class="mt-6 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold" style="background: color-mix(in srgb, var(--site-secondary) 14%, transparent); color: var(--site-secondary);">
                                <x-ui.icon name="star" class="h-4 w-4" /> {{ $tenant->years_of_experience }} سنوات خبرة
                            </div>
                        @endif
                    </section>
                    @break

                @case ('stats')
                    <section id="stats" data-reveal class="px-4 py-16" style="background: color-mix(in srgb, var(--site-primary) 6%, transparent);">
                        <div class="mx-auto max-w-4xl text-center">
                            <h2 class="text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>

                            <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4">
                                <div class="site-card rounded-2xl bg-white/60 p-5" style="border-radius: var(--site-radius);">
                                    <p class="text-4xl font-extrabold" style="color: var(--site-primary);"><span data-count-to="{{ (int) $stats['students'] }}">0</span>+</p>
                                    <p class="mt-2 text-sm opacity-70">طالب</p>
                                </div>
                                <div class="site-card rounded-2xl bg-white/60 p-5" style="border-radius: var(--site-radius);">
                                    <p class="text-4xl font-extrabold" style="color: var(--site-primary);"><span data-count-to="{{ (int) $stats['groups'] }}">0</span></p>
                                    <p class="mt-2 text-sm opacity-70">مجموعة</p>
                                </div>
                                <div class="site-card rounded-2xl bg-white/60 p-5" style="border-radius: var(--site-radius);">
                                    <p class="text-4xl font-extrabold" style="color: var(--site-primary);"><span data-count-to="{{ (int) $stats['subjects'] }}">0</span></p>
                                    <p class="mt-2 text-sm opacity-70">مادة دراسية</p>
                                </div>
                                <div class="site-card rounded-2xl bg-white/60 p-5" style="border-radius: var(--site-radius);">
                                    <p class="text-4xl font-extrabold" style="color: var(--site-primary);"><span data-count-to="{{ (int) ($stats['years'] ?? 0) }}">0</span></p>
                                    <p class="mt-2 text-sm opacity-70">سنوات خبرة</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    @break

                @case ('subjects_grades')
                    <section id="subjects_grades" data-reveal class="mx-auto max-w-5xl px-4 py-16">
                        <h2 class="text-center text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>

                        @if ($publicSubjects->isNotEmpty())
                            <div class="mt-8 flex flex-wrap justify-center gap-3">
                                @foreach ($publicSubjects as $subject)
                                    <span
                                        class="site-chip rounded-full border px-4 py-1.5 text-sm font-medium"
                                        style="border-radius: 9999px; border-color: color-mix(in srgb, var(--site-primary) 30%, transparent);"
                                    >
                                        {{ $subject->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($publicStages->isNotEmpty())
                            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($publicStages as $stage)
                                    <div data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}" class="site-card rounded-2xl border border-black/5 bg-white p-5 shadow-sm" style="border-radius: var(--site-radius);">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--site-primary) 12%, transparent); color: var(--site-primary);">
                                                <x-ui.icon name="academic-cap" class="h-5 w-5" />
                                            </span>
                                            <h3 class="font-bold">{{ $stage->name }}</h3>
                                        </div>
                                        @if ($stage->grades->isNotEmpty())
                                            <ul class="mt-3 space-y-1.5 text-sm opacity-70">
                                                @foreach ($stage->grades as $grade)
                                                    <li class="flex items-center gap-2">
                                                        <x-ui.icon name="check" class="h-3.5 w-3.5 shrink-0" style="color: var(--site-secondary);" /> {{ $grade->name }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                    @break

                @case ('groups')
                    <section id="groups" data-reveal class="px-4 py-16" style="background: color-mix(in srgb, var(--site-primary) 6%, transparent);">
                        <div class="mx-auto max-w-5xl">
                            <h2 class="text-center text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>

                            @if ($publicGroups->isNotEmpty())
                                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($publicGroups as $group)
                                        <div data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}" class="site-card rounded-2xl border border-black/5 bg-white p-5 shadow-sm" style="border-radius: var(--site-radius);">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--site-secondary) 14%, transparent); color: var(--site-secondary);">
                                                    <x-ui.icon name="groups" class="h-5 w-5" />
                                                </span>
                                                <h3 class="font-bold">{{ $group->name }}</h3>
                                            </div>
                                            <p class="mt-2 text-sm opacity-70">{{ $group->subject->name ?? '' }} — {{ $group->grade->name ?? '' }}</p>

                                            <button
                                                type="button"
                                                data-register-trigger
                                                data-group-id="{{ $group->id }}"
                                                data-group-name="{{ $group->name }}"
                                                class="site-btn mt-4 w-full px-4 py-2 text-sm font-semibold text-white"
                                            >
                                                سجّل الآن
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-6 text-center text-sm opacity-60">لا توجد مجموعات متاحة للتسجيل أونلاين حاليًا.</p>
                            @endif
                        </div>
                    </section>
                    @break

                @case ('tips')
                    <section id="tips" data-reveal class="mx-auto max-w-5xl px-4 py-16">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>
                            @if ($tips->isNotEmpty())
                                <a href="{{ route('tenant.website.tips.index', ['tenant' => $tenant->slug]) }}" class="site-nav-link hidden text-sm font-semibold sm:inline-flex sm:items-center sm:gap-1" style="color: var(--site-primary);">
                                    كل المقالات <x-ui.icon name="arrow-left" class="h-4 w-4" />
                                </a>
                            @endif
                        </div>

                        @if ($tips->isNotEmpty())
                            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($tips as $post)
                                    <a href="{{ route('tenant.website.tips.show', ['tenant' => $tenant->slug, 'post' => $post->slug]) }}" data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}" class="site-card block overflow-hidden rounded-2xl border border-black/5 bg-white shadow-sm" style="border-radius: var(--site-radius);">
                                        @if ($post->imageUrl())
                                            <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="h-40 w-full object-cover">
                                        @else
                                            <div class="flex h-40 w-full items-center justify-center" style="background: linear-gradient(135deg, color-mix(in srgb, var(--site-primary) 18%, transparent), color-mix(in srgb, var(--site-secondary) 18%, transparent));">
                                                <x-ui.icon name="book" class="h-10 w-10 opacity-50" />
                                            </div>
                                        @endif
                                        <div class="p-5">
                                            <h3 class="font-bold">{{ $post->title }}</h3>
                                            @if ($post->excerpt)
                                                <p class="mt-1.5 text-sm leading-6 opacity-70">{{ $post->excerpt }}</p>
                                            @endif
                                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold" style="color: var(--site-primary);">
                                                اقرأ المزيد <x-ui.icon name="arrow-left" class="h-3.5 w-3.5" />
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-6 text-center text-sm opacity-60">لا توجد مقالات منشورة بعد.</p>
                        @endif
                    </section>
                    @break

                @case ('testimonials')
                    <section id="testimonials" data-reveal class="px-4 py-16" style="background: color-mix(in srgb, var(--site-primary) 6%, transparent);">
                        <div class="mx-auto max-w-5xl">
                            <h2 class="text-center text-2xl font-bold sm:text-3xl" style="font-size: var(--site-heading-size);">{{ $section->displayTitle() }}</h2>

                            @if ($testimonials->isNotEmpty())
                                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($testimonials as $testimonial)
                                        <div data-reveal data-reveal-delay="{{ min($loop->iteration, 5) }}" class="site-card relative rounded-2xl border border-black/5 bg-white p-6 shadow-sm" style="border-radius: var(--site-radius);">
                                            <x-ui.icon name="quote" class="absolute left-5 top-5 h-7 w-7 opacity-10" />

                                            @if ($testimonial->rating)
                                                <p class="flex gap-0.5 text-amber-400">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <x-ui.icon name="star" class="h-4 w-4 {{ $i > $testimonial->rating ? 'opacity-20' : '' }}" style="{{ $i <= $testimonial->rating ? 'fill: currentColor;' : '' }}" />
                                                    @endfor
                                                </p>
                                            @endif

                                            <p class="relative mt-3 text-sm leading-7 opacity-80">{{ $testimonial->content }}</p>

                                            <div class="mt-5 flex items-center gap-3 border-t border-black/5 pt-4">
                                                @if ($testimonial->studentImageUrl())
                                                    <img src="{{ $testimonial->studentImageUrl() }}" class="h-10 w-10 rounded-full object-cover">
                                                @else
                                                    <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white" style="background: linear-gradient(135deg, var(--site-primary), var(--site-secondary));">
                                                        {{ mb_substr($testimonial->student_name, 0, 1) }}
                                                    </span>
                                                @endif
                                                <div>
                                                    <p class="font-bold">{{ $testimonial->student_name }}</p>
                                                    @if ($testimonial->grade_or_group)
                                                        <p class="text-xs opacity-60">{{ $testimonial->grade_or_group }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-6 text-center text-sm opacity-60">لا توجد آراء منشورة بعد.</p>
                            @endif
                        </div>
                    </section>
                    @break

                @case ('contact')
                    <section id="contact" data-reveal class="mx-auto max-w-4xl px-4 py-16">
                        <div class="relative overflow-hidden rounded-3xl px-6 py-14 text-center text-white" style="background: linear-gradient(135deg, var(--site-primary), var(--site-secondary)); border-radius: calc(var(--site-radius) * 1.5);">
                            <div class="site-float absolute -left-8 -top-8 h-40 w-40 rounded-full bg-white/10"></div>
                            <div class="site-float-slow absolute -bottom-10 -right-10 h-48 w-48 rounded-full bg-white/10"></div>

                            <div class="relative">
                                <h2 class="text-2xl font-bold sm:text-3xl">{{ $section->displayTitle() }}</h2>
                                <p class="mx-auto mt-3 max-w-md text-sm opacity-90">تواصل معنا مباشرة للاستفسار عن المواعيد والمجموعات المتاحة.</p>

                                <div class="mt-7 flex flex-wrap items-center justify-center gap-4 text-sm font-medium">
                                    @if ($tenant->phone)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2">
                                            <x-ui.icon name="phone" class="h-4 w-4" /> {{ $tenant->phone }}
                                        </span>
                                    @endif
                                    @if ($tenant->email)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2">
                                            <x-ui.icon name="mail" class="h-4 w-4" /> {{ $tenant->email }}
                                        </span>
                                    @endif
                                </div>

                                @if ($contactSocialLinks->isNotEmpty())
                                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                                        @foreach ($contactSocialLinks as $link)
                                            <a href="{{ $link->url }}" target="_blank" rel="noopener" aria-label="{{ $link->platformLabel() }}" class="site-social-btn bg-white/15 hover:bg-white/25">
                                                <x-ui.icon name="{{ $link->platform }}" class="h-4 w-4" />
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                    @break
            @endswitch
        @empty
            <section class="mx-auto max-w-3xl px-4 py-20 text-center text-sm opacity-60">
                لا يوجد محتوى منشور على الصفحة الرئيسية بعد.
            </section>
        @endforelse
    </main>

    <div data-register-modal-overlay class="site-modal-overlay"></div>
    <div data-register-modal class="site-modal" role="dialog" aria-modal="true">
        <button type="button" data-register-modal-close aria-label="إغلاق" class="site-modal-close">
            <x-ui.icon name="close" class="h-5 w-5" />
        </button>

        <div data-register-modal-body>
            <h3 class="text-lg font-bold">سجّل الآن</h3>
            <p class="mt-1 text-sm opacity-70">المجموعة: <span data-register-modal-group-name class="font-semibold"></span></p>

            <form data-register-form action="{{ route('tenant.website.register-request', ['tenant' => $tenant->slug]) }}" method="POST" class="mt-5 space-y-4">
                <input type="hidden" name="group_id" data-register-group-id>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">اسم الطالب</label>
                    <input type="text" name="student_name" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p data-error-for="student_name" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">رقم الهاتف</label>
                    <input type="tel" name="phone" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p data-error-for="phone" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">رقم ولي الأمر (اختياري)</label>
                    <input type="tel" name="guardian_phone" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p data-error-for="guardian_phone" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <p data-register-generic-error class="hidden text-sm text-red-600"></p>

                <button type="submit" class="site-btn w-full px-4 py-3 text-sm font-bold text-white">
                    <span data-register-submit-label>إرسال الطلب</span>
                </button>
            </form>

            <div data-register-success class="hidden py-6 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full" style="background: color-mix(in srgb, var(--site-secondary) 16%, transparent); color: var(--site-secondary);">
                    <x-ui.icon name="check" class="h-7 w-7" />
                </span>
                <p data-register-success-message class="mt-4 text-sm leading-7 opacity-80"></p>
            </div>
        </div>
    </div>

    <x-website.footer :settings="$settings" :tenant="$tenant" :footer-social-links="$footerSocialLinks" />
</body>
</html>

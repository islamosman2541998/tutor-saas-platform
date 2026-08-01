@php
    // Super Admin has no tenant to theme — defaults() renders identically
    // to the pre-appearance-settings hardcoded design either way, so this
    // is a safe fallback rather than a special case to branch on below.
    $dashAppearance = isset($currentTenant)
        ? (\App\Models\DashboardAppearanceSettings::query()->first() ?? \App\Models\DashboardAppearanceSettings::defaults())
        : \App\Models\DashboardAppearanceSettings::defaults();
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-default-theme="{{ $dashAppearance->theme_mode ?? 'user_choice' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    {{-- Must run before any stylesheet paints, so it stays inline rather
         than living in the bundled theme.js — see resources/js/theme.js
         for the toggle button / theme-changed event that runs after load. --}}
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var mode = stored || document.documentElement.dataset.defaultTheme || 'user_choice';
            var dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @if ($dashAppearance->faviconUrl())
        <link rel="icon" href="{{ $dashAppearance->faviconUrl() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            @foreach ($dashAppearance->cssVariables() as $name => $value)
                {{ $name }}: {{ $value }};
            @endforeach
        }
        /* Structural/surface colors get a fixed, professional dark palette;
           the tenant's brand colors (primary/secondary/sidebar-active) are
           deliberately left untouched so accents stay on-brand in both
           modes — see the design-refresh plan for the reasoning. */
        .dark {
            --dash-sidebar-bg: #1e293b;
            --dash-sidebar-text: #cbd5e1;
            --dash-topbar: #1e293b;
            --dash-page-bg: #0f172a;
            --dash-card-bg: #1e293b;
            --dash-text: #e2e8f0;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased" style="background: var(--dash-page-bg); color: var(--dash-text);">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 right-0 z-40 flex w-72 {{ $dashAppearance->sidebarWidthClass() }} flex-col border-l border-slate-200 shadow-sm transition-transform duration-200 ease-out dark:border-slate-700 lg:translate-x-0"
            style="background: var(--dash-sidebar-bg);"
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <div
                class="flex items-center justify-between gap-3 border-b px-5 py-5"
                style="border-color: color-mix(in srgb, var(--dash-sidebar-text) 10%, transparent);"
            >
                <div class="flex min-w-0 items-center gap-3">
                    @if ($dashAppearance->logoMiniUrl())
                        <img src="{{ $dashAppearance->logoMiniUrl() }}" alt="" class="h-10 w-10 shrink-0 rounded-xl object-contain">
                    @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center text-base font-bold text-white shadow-sm" style="background: var(--dash-primary); border-radius: var(--dash-radius, 12px);">
                            {{ mb_substr(auth()->user()?->isSuperAdmin() ? config('app.name') : ($currentTenant->teacher_name ?? config('app.name')), 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold" style="color: var(--dash-sidebar-text);">
                            {{ auth()->user()?->isSuperAdmin() ? 'لوحة المنصة' : ($currentTenant->teacher_name ?? config('app.name')) }}
                        </p>
                        <p class="truncate text-xs opacity-60" style="color: var(--dash-sidebar-text);">
                            {{ auth()->user()?->isSuperAdmin() ? 'Super Admin' : ($currentTenant->name ?? '') }}
                        </p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 lg:hidden">
                    <x-ui.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                @auth
                    @if (auth()->user()->isSuperAdmin())
                        <div class="space-y-1">
                            <x-ui.nav-link href="{{ route('admin.dashboard') }}" icon="home" :active="request()->routeIs('admin.dashboard')">
                                الرئيسية
                            </x-ui.nav-link>
                            <x-ui.nav-link href="{{ route('admin.tenants') }}" icon="building" :active="request()->routeIs('admin.tenants')">
                                المدرسون
                            </x-ui.nav-link>
                            <x-ui.nav-link href="{{ route('admin.plans') }}" icon="banknotes" :active="request()->routeIs('admin.plans')">
                                الباقات
                            </x-ui.nav-link>
                            <x-ui.nav-link href="{{ route('admin.subscriptions') }}" icon="banknotes" :active="request()->routeIs('admin.subscriptions')">
                                المدفوعات والاشتراكات
                            </x-ui.nav-link>
                        </div>
                    @elseif (isset($currentTenant))
                        <div class="space-y-1">
                            <x-ui.nav-link href="{{ route('tenant.dashboard', ['tenant' => $currentTenant->slug]) }}" icon="home" :active="request()->routeIs('tenant.dashboard')">
                                الرئيسية
                            </x-ui.nav-link>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">إدارة الدروس</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.students', ['tenant' => $currentTenant->slug]) }}" icon="users" :active="request()->routeIs('tenant.students*')">
                                    الطلاب
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.groups', ['tenant' => $currentTenant->slug]) }}" icon="groups" :active="request()->routeIs('tenant.groups*')">
                                    المجموعات
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.registration-requests', ['tenant' => $currentTenant->slug]) }}" icon="users" :active="request()->routeIs('tenant.registration-requests*')">
                                    طلبات التسجيل
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">المالية</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.dues', ['tenant' => $currentTenant->slug]) }}" icon="banknotes" :active="request()->routeIs('tenant.dues*')">
                                    المستحقات الشهرية
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.payments', ['tenant' => $currentTenant->slug]) }}" icon="banknotes" :active="request()->routeIs('tenant.payments*')">
                                    الدفعات
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.overdue', ['tenant' => $currentTenant->slug]) }}" icon="banknotes" :active="request()->routeIs('tenant.overdue*')">
                                    المتأخرات
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">التقارير</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.reports', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.reports*')">
                                    التقارير والتصدير
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">الموقع التعريفي</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.website-settings', ['tenant' => $currentTenant->slug]) }}" icon="building" :active="request()->routeIs('tenant.website-settings*')">
                                    إعدادات الموقع
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.navbar', ['tenant' => $currentTenant->slug]) }}" icon="map-pin" :active="request()->routeIs('tenant.website.navbar*')">
                                    شريط التنقّل
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.social-links', ['tenant' => $currentTenant->slug]) }}" icon="map-pin" :active="request()->routeIs('tenant.website.social-links*')">
                                    روابط التواصل
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.sections', ['tenant' => $currentTenant->slug]) }}" icon="calendar" :active="request()->routeIs('tenant.website.sections*')">
                                    أقسام الصفحة الرئيسية
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.sliders', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.website.sliders*')">
                                    السلايدر الرئيسي
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.posts', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.website.posts*')">
                                    النصائح والمقالات
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.testimonials', ['tenant' => $currentTenant->slug]) }}" icon="users" :active="request()->routeIs('tenant.website.testimonials*')">
                                    آراء الطلاب
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.website.pages', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.website.pages*')">
                                    الصفحات الثابتة
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">المظهر</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.appearance.dashboard', ['tenant' => $currentTenant->slug]) }}" icon="building" :active="request()->routeIs('tenant.appearance.dashboard*')">
                                    مظهر لوحة التحكم
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.appearance.login', ['tenant' => $currentTenant->slug]) }}" icon="building" :active="request()->routeIs('tenant.appearance.login*')">
                                    مظهر صفحة الدخول
                                </x-ui.nav-link>
                            </div>
                        </div>

                        @if (auth()->user()->can('users.manage') || auth()->user()->can('activity.view'))
                            <div>
                                <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">الفريق</p>
                                <div class="space-y-1">
                                    @can('users.manage')
                                        <x-ui.nav-link href="{{ route('tenant.users', ['tenant' => $currentTenant->slug]) }}" icon="users" :active="request()->routeIs('tenant.users*')">
                                            المستخدمون
                                        </x-ui.nav-link>
                                    @endcan
                                    @can('activity.view')
                                        <x-ui.nav-link href="{{ route('tenant.activity-log', ['tenant' => $currentTenant->slug]) }}" icon="calendar" :active="request()->routeIs('tenant.activity-log*')">
                                            سجل النشاط
                                        </x-ui.nav-link>
                                    @endcan
                                </div>
                            </div>
                        @endif

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">الإعدادات الأكاديمية</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.academic-years', ['tenant' => $currentTenant->slug]) }}" icon="calendar" :active="request()->routeIs('tenant.academic-years')">
                                    السنوات الدراسية
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.stages', ['tenant' => $currentTenant->slug]) }}" icon="academic-cap" :active="request()->routeIs('tenant.stages*')">
                                    المراحل والصفوف
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.subjects', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.subjects')">
                                    المواد
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.locations', ['tenant' => $currentTenant->slug]) }}" icon="map-pin" :active="request()->routeIs('tenant.locations')">
                                    أماكن التدريس
                                </x-ui.nav-link>
                            </div>
                        </div>
                    @endif
                @endauth
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="flex flex-1 flex-col {{ $dashAppearance->sidebarMarginClass() }}">
            <header
                class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 backdrop-blur dark:border-slate-700 sm:px-6 lg:px-10"
                style="background: color-mix(in srgb, var(--dash-topbar) 85%, transparent);"
            >
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 lg:hidden">
                        <x-ui.icon name="menu" class="h-6 w-6" />
                    </button>
                    <div class="lg:hidden">
                        @if ($dashAppearance->logoFullUrl())
                            <img src="{{ $dashAppearance->logoFullUrl() }}" alt="" class="h-6 w-auto">
                        @else
                            <span class="font-bold" style="color: var(--dash-primary);">{{ config('app.name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        onclick="toggleTheme()"
                        class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                        aria-label="تبديل الوضع الليلي/النهاري"
                    >
                        <x-ui.icon name="moon" class="h-5 w-5 dark:hidden" />
                        <x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" />
                    </button>

                    @auth
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                @click.outside="open = false"
                                class="flex items-center gap-2 rounded-full py-1 pe-1 ps-1.5 transition hover:bg-slate-100 dark:hover:bg-slate-700 sm:pe-2.5"
                            >
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                                    style="background: var(--dash-primary);"
                                >
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden max-w-[10rem] truncate text-sm font-medium text-slate-700 dark:text-slate-200 sm:block">
                                    {{ auth()->user()->name }}
                                </span>
                                <x-ui.icon
                                    name="chevron-down"
                                    class="hidden h-4 w-4 text-slate-400 transition dark:text-slate-500 sm:block"
                                    x-bind:class="open ? 'rotate-180' : ''"
                                />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition.origin.top.left
                                class="absolute end-0 z-30 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800"
                            >
                                <div class="border-b border-slate-100 px-3.5 py-2.5 dark:border-slate-700">
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ auth()->user()->isSuperAdmin() ? route('admin.logout') : route('tenant.logout', ['tenant' => $currentTenant->slug ?? null]) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <x-ui.icon name="logout" class="h-4 w-4 shrink-0" />
                                        تسجيل الخروج
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
                <div class="mx-auto max-w-6xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>

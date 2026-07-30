@php
    // Super Admin has no tenant to theme — defaults() renders identically
    // to the pre-appearance-settings hardcoded design either way, so this
    // is a safe fallback rather than a special case to branch on below.
    $dashAppearance = isset($currentTenant)
        ? (\App\Models\DashboardAppearanceSettings::query()->first() ?? \App\Models\DashboardAppearanceSettings::defaults())
        : \App\Models\DashboardAppearanceSettings::defaults();
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
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
            class="fixed inset-y-0 right-0 z-40 flex w-72 {{ $dashAppearance->sidebarWidthClass() }} flex-col border-l border-slate-200 transition-transform duration-200 ease-out lg:translate-x-0"
            style="background: var(--dash-sidebar-bg);"
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="flex items-center justify-between gap-3 px-5 py-5">
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
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-600 lg:hidden">
                    <x-ui.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-5">
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
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">إدارة الدروس</p>
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
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">المالية</p>
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
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">التقارير</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.reports', ['tenant' => $currentTenant->slug]) }}" icon="book" :active="request()->routeIs('tenant.reports*')">
                                    التقارير والتصدير
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">الموقع التعريفي</p>
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
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">المظهر</p>
                            <div class="space-y-1">
                                <x-ui.nav-link href="{{ route('tenant.appearance.dashboard', ['tenant' => $currentTenant->slug]) }}" icon="building" :active="request()->routeIs('tenant.appearance.dashboard*')">
                                    مظهر لوحة التحكم
                                </x-ui.nav-link>
                                <x-ui.nav-link href="{{ route('tenant.appearance.login', ['tenant' => $currentTenant->slug]) }}" icon="building" :active="request()->routeIs('tenant.appearance.login*')">
                                    مظهر صفحة الدخول
                                </x-ui.nav-link>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold text-slate-400">الإعدادات الأكاديمية</p>
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

            @auth
                <div class="border-t p-3" style="border-color: color-mix(in srgb, var(--dash-sidebar-text) 12%, transparent);">
                    <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold" style="background: color-mix(in srgb, var(--dash-sidebar-text) 12%, transparent); color: var(--dash-sidebar-text);">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <p class="truncate text-sm font-medium" style="color: var(--dash-sidebar-text);">{{ auth()->user()->name }}</p>
                    </div>

                    <form method="POST" action="{{ auth()->user()->isSuperAdmin() ? route('admin.logout') : route('tenant.logout', ['tenant' => $currentTenant->slug ?? null]) }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium opacity-80 transition hover:bg-red-50 hover:text-red-600 hover:opacity-100" style="color: var(--dash-sidebar-text);">
                            <x-ui.icon name="logout" class="h-5 w-5 shrink-0" />
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        {{-- Main content --}}
        <div class="flex flex-1 flex-col {{ $dashAppearance->sidebarMarginClass() }}">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 px-4 py-3 backdrop-blur lg:hidden" style="background: color-mix(in srgb, var(--dash-topbar) 80%, transparent);">
                <button @click="sidebarOpen = true" class="text-slate-500 hover:text-slate-700">
                    <x-ui.icon name="menu" class="h-6 w-6" />
                </button>
                @if ($dashAppearance->logoFullUrl())
                    <img src="{{ $dashAppearance->logoFullUrl() }}" alt="" class="h-6 w-auto">
                @else
                    <span class="font-bold" style="color: var(--dash-primary);">{{ config('app.name') }}</span>
                @endif
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

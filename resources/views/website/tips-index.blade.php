<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>النصائح والمقالات — {{ $settings->site_name ?: $tenant->teacher_name }}</title>
    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/website.js'])
    <x-website.head-styles :settings="$settings" />
</head>
<body class="min-h-screen antialiased">
    <x-website.header :settings="$settings" :tenant="$tenant" />

    <main class="mx-auto max-w-5xl px-4 py-12">
        <a href="{{ route('tenant.website.home', ['tenant' => $tenant->slug]) }}" class="site-nav-link inline-flex items-center gap-1.5 text-sm opacity-70">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> الرئيسية
        </a>

        <h1 data-reveal class="mt-5 text-center text-3xl font-bold" style="font-size: var(--site-heading-size);">النصائح والمقالات</h1>

        @if ($tips->isNotEmpty())
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
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

            <div class="mt-10">
                {{ $tips->links() }}
            </div>
        @else
            <p class="mt-6 text-center text-sm opacity-60">لا توجد مقالات منشورة بعد.</p>
        @endif
    </main>

    <x-website.footer :settings="$settings" :tenant="$tenant" />
</body>
</html>

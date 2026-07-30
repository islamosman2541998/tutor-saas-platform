<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->meta_title ?: $page->title }}</title>
    @if ($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/website.js'])
    <x-website.head-styles :settings="$settings" />
</head>
<body class="min-h-screen antialiased">
    <x-website.header :settings="$settings" :tenant="$page->tenant" />

    <main class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('tenant.website.home', ['tenant' => $page->tenant->slug]) }}" class="site-nav-link inline-flex items-center gap-1.5 text-sm opacity-70">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> الرئيسية
        </a>

        <div data-reveal>
            <h1 class="mt-6 text-3xl font-bold" style="font-size: var(--site-heading-size);">{{ $page->title }}</h1>

            <div class="prose mt-6 max-w-none whitespace-pre-line text-base leading-8 opacity-90">{{ $page->content }}</div>
        </div>
    </main>

    <x-website.footer :settings="$settings" :tenant="$page->tenant" />
</body>
</html>

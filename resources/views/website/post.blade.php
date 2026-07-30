<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->meta_title ?: $post->title }}</title>
    @if ($post->meta_description)
        <meta name="description" content="{{ $post->meta_description }}">
    @endif
    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/website.js'])
    <x-website.head-styles :settings="$settings" />
</head>
<body class="min-h-screen antialiased">
    <x-website.header :settings="$settings" :tenant="$post->tenant" />

    <main class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('tenant.website.tips.index', ['tenant' => $post->tenant->slug]) }}" class="site-nav-link inline-flex items-center gap-1.5 text-sm opacity-70">
            <x-ui.icon name="arrow-left" class="h-4 w-4" /> كل النصائح والمقالات
        </a>

        <div data-reveal>
            @if ($post->imageUrl())
                <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="mt-5 w-full rounded-2xl object-cover shadow-sm" style="border-radius: var(--site-radius); max-height: 380px;">
            @endif

            <h1 class="mt-6 text-3xl font-bold" style="font-size: var(--site-heading-size);">{{ $post->title }}</h1>

            <p class="mt-2 flex items-center gap-2 text-sm opacity-60">
                <x-ui.icon name="calendar" class="h-4 w-4" /> {{ $post->published_at?->format('Y-m-d') }}
                @if ($post->author)
                    — {{ $post->author->name }}
                @endif
            </p>

            <div class="prose mt-6 max-w-none whitespace-pre-line text-base leading-8 opacity-90">{{ $post->content }}</div>
        </div>
    </main>

    <x-website.footer :settings="$settings" :tenant="$post->tenant" />
</body>
</html>

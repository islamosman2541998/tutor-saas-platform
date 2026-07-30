@props(['settings', 'tenant', 'navbarItems' => null, 'navSocialLinks' => null, 'navPages' => null])

@php
    $navbarItems = $navbarItems ?? \App\Models\NavbarItem::query()->where('is_visible', true)->orderBy('sort_order')->get();
    $navSocialLinks = $navSocialLinks ?? \App\Models\SocialLink::query()->visibleIn('navbar')->orderBy('sort_order')->get();
    $navPages = $navPages ?? \App\Models\Page::query()->publicly()->where('show_in_navbar', true)->orderBy('sort_order')->get();
    $hasNav = $navbarItems->isNotEmpty() || $navSocialLinks->isNotEmpty() || $navPages->isNotEmpty();
@endphp

<header
    data-site-header
    style="background: color-mix(in srgb, var(--site-navbar) 92%, transparent); backdrop-filter: blur(10px);"
    class="sticky top-0 z-40 border-b border-black/5"
>
    <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
        <a href="{{ route('tenant.website.home', ['tenant' => $tenant->slug]) }}" class="flex items-center gap-3">
            @if ($settings->logoUrl())
                <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-10 w-auto">
            @else
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                    style="background: linear-gradient(135deg, var(--site-primary), var(--site-secondary));"
                >
                    {{ mb_substr($tenant->teacher_name, 0, 1) }}
                </span>
            @endif
            <span class="text-lg font-bold">{{ $tenant->teacher_name }}</span>
        </a>

        @if ($hasNav)
            <button
                type="button"
                data-nav-toggle
                aria-expanded="false"
                aria-label="فتح القائمة"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-black/10 md:hidden"
            >
                <x-ui.icon name="menu" class="h-5 w-5" />
            </button>

            <nav data-nav-menu class="flex w-full flex-col gap-4 text-sm font-medium md:w-auto md:flex-row md:items-center md:gap-7">
                @foreach ($navbarItems as $item)
                    @php
                        $itemHref = match ($item->type) {
                            'external' => $item->href(),
                            'page' => $item->target_key ? route('tenant.website.pages.show', ['tenant' => $tenant->slug, 'page' => $item->target_key]) : '#',
                            default => match ($item->target_key) {
                                'home' => route('tenant.website.home', ['tenant' => $tenant->slug]),
                                'tips' => route('tenant.website.tips.index', ['tenant' => $tenant->slug]),
                                default => route('tenant.website.home', ['tenant' => $tenant->slug]).'#'.$item->target_key,
                            },
                        };
                    @endphp
                    <a href="{{ $itemHref }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="site-nav-link opacity-80">
                        {{ $item->label }}
                    </a>
                @endforeach

                @foreach ($navPages as $navPage)
                    <a href="{{ route('tenant.website.pages.show', ['tenant' => $tenant->slug, 'page' => $navPage->slug]) }}" class="site-nav-link opacity-80">
                        {{ $navPage->title }}
                    </a>
                @endforeach

                @if ($navSocialLinks->isNotEmpty())
                    <span class="flex items-center gap-2 md:border-r md:border-current/15 md:pr-6">
                        @foreach ($navSocialLinks as $link)
                            <a
                                href="{{ $link->url }}"
                                target="_blank"
                                rel="noopener"
                                aria-label="{{ $link->platformLabel() }}"
                                class="site-social-btn opacity-70 hover:opacity-100"
                                style="background: color-mix(in srgb, var(--site-primary) 10%, transparent);"
                            >
                                <x-ui.icon name="{{ $link->platform }}" class="h-4 w-4" />
                            </a>
                        @endforeach
                    </span>
                @endif
            </nav>
        @endif
    </div>
</header>

@props(['settings', 'tenant', 'footerSocialLinks' => null, 'footerPages' => null])

@php
    $footerSocialLinks = $footerSocialLinks ?? \App\Models\SocialLink::query()->visibleIn('footer')->orderBy('sort_order')->get();
    $footerPages = $footerPages ?? \App\Models\Page::query()->publicly()->where('show_in_footer', true)->orderBy('sort_order')->get();
@endphp

<footer style="background: var(--site-footer); color: #fff;" class="relative mt-16 overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-px" style="background: linear-gradient(90deg, transparent, var(--site-primary), transparent);"></div>

    <div class="mx-auto max-w-5xl px-4 py-14">
        <div class="grid gap-10 text-center sm:grid-cols-3 sm:text-right">
            <div>
                <p class="text-lg font-bold">{{ $settings->site_name ?: $tenant->teacher_name }}</p>
                @if ($settings->short_description)
                    <p class="mt-2 text-sm leading-6 opacity-70">{{ \Illuminate\Support\Str::limit($settings->short_description, 110) }}</p>
                @endif
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide opacity-60">تواصل معنا</p>
                <div class="mt-3 space-y-2 text-sm opacity-80">
                    @if ($tenant->phone)
                        <p class="flex items-center justify-center gap-2 sm:justify-start">
                            <x-ui.icon name="phone" class="h-4 w-4 opacity-60" /> {{ $tenant->phone }}
                        </p>
                    @endif
                    @if ($tenant->email)
                        <p class="flex items-center justify-center gap-2 sm:justify-start">
                            <x-ui.icon name="mail" class="h-4 w-4 opacity-60" /> {{ $tenant->email }}
                        </p>
                    @endif
                </div>
            </div>

            <div>
                @if ($footerPages->isNotEmpty())
                    <p class="text-sm font-semibold uppercase tracking-wide opacity-60">روابط</p>
                    <div class="mt-3 flex flex-col items-center gap-2 text-sm opacity-80 sm:items-start">
                        @foreach ($footerPages as $footerPage)
                            <a href="{{ route('tenant.website.pages.show', ['tenant' => $tenant->slug, 'page' => $footerPage->slug]) }}" class="hover:opacity-100">{{ $footerPage->title }}</a>
                        @endforeach
                    </div>
                @endif

                @if ($footerSocialLinks->isNotEmpty())
                    <div class="mt-5 flex items-center justify-center gap-2 sm:justify-start">
                        @foreach ($footerSocialLinks as $link)
                            <a
                                href="{{ $link->url }}"
                                target="_blank"
                                rel="noopener"
                                aria-label="{{ $link->platformLabel() }}"
                                class="site-social-btn bg-white/10 hover:bg-white/20"
                            >
                                <x-ui.icon name="{{ $link->platform }}" class="h-4 w-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <p class="mt-10 border-t border-white/10 pt-6 text-center text-xs opacity-50">
            © {{ now()->year }} {{ $settings->site_name ?: $tenant->teacher_name }} — جميع الحقوق محفوظة
        </p>
    </div>
</footer>

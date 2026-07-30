@props(['href', 'icon', 'active' => false])

<a
    href="{{ $href }}"
    class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition hover:[background:color-mix(in_srgb,var(--dash-sidebar-text,#475569)_8%,transparent)]"
    style="border-radius: var(--dash-radius, 12px); {{ $active
        ? 'background: color-mix(in srgb, var(--dash-sidebar-active, #4f46e5) 12%, transparent); color: var(--dash-sidebar-active, #4f46e5);'
        : 'color: var(--dash-sidebar-text, #475569);' }}"
>
    <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0 transition" style="{{ $active
        ? 'color: var(--dash-sidebar-active, #4f46e5);'
        : 'color: var(--dash-sidebar-text, #475569); opacity: 0.6;' }}" />
    <span class="truncate">{{ $slot }}</span>
</a>

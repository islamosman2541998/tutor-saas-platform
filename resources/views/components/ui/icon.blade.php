@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.6']) }}>
    @switch($name)
        @case('home')
            <path d="M4 10.5 12 4l8 6.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M6 9.5V19a1 1 0 0 0 1 1h3v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5h3a1 1 0 0 0 1-1V9.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('users')
            <circle cx="9" cy="8" r="3" />
            <path d="M3.5 20a5.5 5.5 0 0 1 11 0" stroke-linecap="round" />
            <circle cx="17" cy="9" r="2.3" />
            <path d="M15.5 13.2a4.2 4.2 0 0 1 5.5 4" stroke-linecap="round" />
            @break

        @case('groups')
            <rect x="4" y="4" width="16" height="6" rx="1.5" />
            <rect x="4" y="14" width="16" height="6" rx="1.5" />
            @break

        @case('calendar')
            <rect x="4" y="5.5" width="16" height="14" rx="1.5" />
            <path d="M4 9.5h16" stroke-linecap="round" />
            <path d="M8 3.5v3.5M16 3.5v3.5" stroke-linecap="round" />
            @break

        @case('academic-cap')
            <path d="M2.5 9.5 12 5l9.5 4.5-9.5 4.5-9.5-4.5Z" stroke-linejoin="round" />
            <path d="M6.5 11.5v4c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5v-4" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('book')
            <path d="M4 5.5A2 2 0 0 1 6 4h6v16H6a2 2 0 0 0-2 2z" stroke-linejoin="round" />
            <path d="M20 5.5A2 2 0 0 0 18 4h-6v16h6a2 2 0 0 1 2 2z" stroke-linejoin="round" />
            @break

        @case('map-pin')
            <path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z" stroke-linejoin="round" />
            <circle cx="12" cy="9.5" r="2.3" />
            @break

        @case('building')
            <rect x="5" y="3.5" width="10" height="17" rx="1" />
            <path d="M15 9h4v11.5h-4" stroke-linejoin="round" />
            <path d="M8 7.5h1M11 7.5h1M8 11h1M11 11h1M8 14.5h1M11 14.5h1" stroke-linecap="round" />
            @break

        @case('banknotes')
            <rect x="2.5" y="7" width="19" height="11" rx="1.8" />
            <circle cx="12" cy="12.5" r="2.6" />
            @break

        @case('logout')
            <path d="M15 4.5H7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20 12H9.5M20 12l-3.5-3.5M20 12l-3.5 3.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
            @break

        @case('close')
            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
            @break

        @case('chevron-down')
            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('phone')
            <path d="M5.5 4.5h3l1.5 4-2 1.5a11 11 0 0 0 5 5l1.5-2 4 1.5v3a1.5 1.5 0 0 1-1.6 1.5A16 16 0 0 1 4 6.1 1.5 1.5 0 0 1 5.5 4.5Z" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('mail')
            <rect x="3" y="5.5" width="18" height="13" rx="1.8" />
            <path d="m4 6.5 8 6.5 8-6.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('quote')
            <path d="M8.5 8.5c-2.5 0-4 2-4 4.5s1.5 4 3.5 4c1.5 0 2.5-1 2.5-2.5 0-1.2-.8-2-2-2.2.2-1.8 1.5-3 3.5-3.3V8.5c-1.2 0-2.5.2-3.5 0Zm9 0c-2.5 0-4 2-4 4.5s1.5 4 3.5 4c1.5 0 2.5-1 2.5-2.5 0-1.2-.8-2-2-2.2.2-1.8 1.5-3 3.5-3.3V8.5c-1.2 0-2.5.2-3.5 0Z" />
            @break

        @case('star')
            <path d="m12 3.5 2.6 5.4 5.9.8-4.3 4.2 1 5.9L12 17l-5.2 2.8 1-5.9-4.3-4.2 5.9-.8Z" stroke-linejoin="round" />
            @break

        @case('arrow-left')
            <path d="M20 12H4M4 12l6-6M4 12l6 6" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('check')
            <path d="m5 12.5 4.5 4.5L19 7" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('sparkle')
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18" stroke-linecap="round" />
            @break

        @case('facebook')
            <path d="M14 21v-7h2.5l.5-3H14V9c0-.9.3-1.5 1.7-1.5H17V4.8c-.3 0-1.3-.1-2.4-.1-2.4 0-4.1 1.5-4.1 4.2V11H8v3h2.5v7Z" stroke-linejoin="round" />
            @break

        @case('instagram')
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17" cy="7" r="1" fill="currentColor" stroke="none" />
            @break

        @case('whatsapp')
            <path d="M6.5 17.5 5 20l2.6-1.4A8 8 0 1 0 4.5 12 8 8 0 0 0 6.5 17.5Z" stroke-linejoin="round" />
            <path d="M9 10c.3 2.5 2.5 4.7 5 5 .8.1 1.5-.5 1.2-1.2-.1-.4-.5-1.4-.9-1.5-.3-.1-.7.2-1 .4-1-.4-1.8-1.2-2.2-2.2.2-.3.5-.7.4-1-.1-.4-1.1-1.9-1.5-2-.4-.1-1 .3-1.2.7-.2.4.2.8.2 1.3Z" />
            @break

        @case('youtube')
            <rect x="2.5" y="6" width="19" height="12" rx="3" />
            <path d="M10.5 9.5v5l4.5-2.5Z" fill="currentColor" stroke="none" />
            @break

        @case('tiktok')
            <path d="M14 4v9.5a2.5 2.5 0 1 1-2.5-2.5" stroke-linecap="round" />
            <path d="M14 4c.3 2 1.8 3.5 3.5 3.7" stroke-linecap="round" />
            @break

        @case('telegram')
            <path d="m3.5 12 16-7-3 15-5-4-2.5 2.3-.3-4.3Z" stroke-linejoin="round" />
            <path d="m8.7 13.7 8-6.7" stroke-linecap="round" />
            @break

        @case('linkedin')
            <rect x="3.5" y="3.5" width="17" height="17" rx="2.5" />
            <circle cx="8" cy="8.2" r="1" fill="currentColor" stroke="none" />
            <path d="M8 11v6M12 11v6M12 13.5c0-1.4 1-2.5 2.3-2.5S16.5 12 16.5 13.5V17" stroke-linecap="round" />
            @break

        @case('x')
            <path d="M5 5l14 14M19 5 5 19" stroke-linecap="round" />
            @break
    @endswitch
</svg>

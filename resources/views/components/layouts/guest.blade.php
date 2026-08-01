@php
    // Central admin login/register share this layout too and have no
    // tenant to theme — defaults() renders identically to the
    // pre-appearance-settings hardcoded design, so it's a safe fallback.
    $loginSettings = isset($currentTenant)
        ? (\App\Models\LoginPageSettings::query()->first() ?? \App\Models\LoginPageSettings::defaults())
        : \App\Models\LoginPageSettings::defaults();

    $justify = match ($loginSettings->card_position) {
        'left' => 'justify-content: flex-start;',
        'right' => 'justify-content: flex-end;',
        default => 'justify-content: center;',
    };
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-default-theme="{{ $loginSettings->theme_mode ?? 'user_choice' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    {{-- Must run before any stylesheet paints — see app.blade.php for the
         matching copy and resources/js/theme.js for the toggle button. --}}
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var mode = stored || document.documentElement.dataset.defaultTheme || 'user_choice';
            var dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            @foreach ($loginSettings->cssVariables() as $name => $value)
                {{ $name }}: {{ $value }};
            @endforeach
        }
        /* Surface colors get a fixed dark palette; button/focus colors stay
           on the tenant's brand choice in both modes (see app.blade.php's
           equivalent .dark block for the same reasoning). */
        .dark {
            --login-bg-color: #0f172a;
            --login-card-bg: #1e293b;
            --login-title: #f1f5f9;
            --login-text: #94a3b8;
            --login-label: #cbd5e1;
            --login-input-bg: #334155;
            --login-input-text: #f1f5f9;
            --login-input-border: #475569;
        }
        /* Scoped override for the shared x-ui.input styling — deliberately
           not a global x-ui.input refactor (that component is used all
           over the tenant dashboard, where a different, independent
           color scheme applies) — see DashboardAppearanceSettings for that
           one. This only touches inputs inside the login card. */
        .login-card input:not([type="checkbox"]) {
            background: var(--login-input-bg) !important;
            color: var(--login-input-text) !important;
            border-color: var(--login-input-border) !important;
            border-radius: var(--login-radius, 8px) !important;
        }
        .login-card input:not([type="checkbox"]):focus {
            border-color: var(--login-input-focus) !important;
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--login-input-focus) 25%, transparent) !important;
        }
        .login-card label { color: var(--login-label); }
        .login-card .login-title { color: var(--login-title); }
        .login-card .login-text, .login-card .login-text a { color: var(--login-text); }
        .login-card button[type="submit"] {
            background: var(--login-button) !important;
            color: var(--login-button-text) !important;
            border-radius: var(--login-radius, 8px) !important;
        }
        .login-card button[type="submit"]:hover {
            background: var(--login-button-hover) !important;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased" style="background: var(--login-bg-color);">
    <button
        type="button"
        onclick="toggleTheme()"
        class="fixed end-4 top-4 z-20 rounded-full bg-white/80 p-2.5 text-slate-500 shadow-sm backdrop-blur transition hover:bg-white dark:bg-slate-800/80 dark:text-slate-400 dark:hover:bg-slate-800"
        aria-label="تبديل الوضع الليلي/النهاري"
    >
        <x-ui.icon name="moon" class="h-5 w-5 dark:hidden" />
        <x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" />
    </button>

    <div class="relative flex min-h-screen flex-col items-center px-4 py-10 md:flex-row" style="{{ $justify }}">
        @if ($loginSettings->backgroundImageUrl())
            <div class="absolute inset-0" style="background: url('{{ $loginSettings->backgroundImageUrl() }}') center/cover;"></div>
            @if ($loginSettings->overlay_enabled)
                <div class="absolute inset-0" style="background: #000; opacity: var(--login-overlay-opacity);"></div>
            @endif
        @endif

        @if ($loginSettings->sideImageUrl() && $loginSettings->card_position !== 'center')
            <img
                src="{{ $loginSettings->sideImageUrl() }}"
                alt=""
                class="relative z-10 hidden h-[28rem] w-96 rounded-2xl object-cover md:block {{ $loginSettings->card_position === 'left' ? 'ml-6 order-2' : 'mr-6' }}"
                style="border-radius: var(--login-radius);"
            >
        @endif

        <div class="login-card relative z-10 w-full max-w-sm p-6 sm:p-8" style="background: var(--login-card-bg); opacity: var(--login-card-opacity); border-radius: var(--login-radius); box-shadow: var(--login-shadow); {{ $loginSettings->card_blur ? 'backdrop-filter: blur(12px);' : '' }}">
            @if ($loginSettings->show_logo)
                <div class="mb-6 text-lg font-bold login-title">{{ $currentTenant->teacher_name ?? config('app.name') }}</div>
            @endif

            @if ($loginSettings->welcome_title)
                <p class="mb-1 text-base font-bold login-title">{{ $loginSettings->welcome_title }}</p>
            @endif
            @if ($loginSettings->welcome_description)
                <p class="mb-4 text-sm login-text">{{ $loginSettings->welcome_description }}</p>
            @endif

            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>

@props(['variant' => 'primary'])

@php
$base = 'inline-flex w-full items-center justify-center px-4 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60';
$variants = [
    'primary' => 'text-white focus:outline-none focus:ring-2 hover:[background:color-mix(in_srgb,var(--dash-primary,#4f46e5)_85%,black)]',
    'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-700 dark:focus:ring-slate-600',
];
$style = $variant === 'primary'
    ? 'border-radius: var(--dash-radius, 8px); background: var(--dash-primary, #4f46e5);'
    : 'border-radius: var(--dash-radius, 8px);';
@endphp

<button {{ $attributes->merge(['class' => $base . ' ' . $variants[$variant], 'style' => $style]) }}>
    {{ $slot }}
</button>

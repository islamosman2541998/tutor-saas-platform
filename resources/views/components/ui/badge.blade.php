@props(['color' => 'slate'])

@php
$colors = [
    'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    'green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
    'red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
    'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
    'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . ($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>

@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800 ' . ($padded ? 'p-5' : '')]) }}>
    {{ $slot }}
</div>

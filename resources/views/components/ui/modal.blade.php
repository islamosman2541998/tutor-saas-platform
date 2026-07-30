@props(['show' => false, 'title' => null, 'onClose' => 'closeModal'])

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" wire:click="{{ $onClose }}"></div>

        <div class="relative w-full max-w-lg p-6 shadow-xl sm:p-8" style="background: var(--dash-card-bg, #ffffff); border-radius: var(--dash-radius, 16px); color: var(--dash-text, #1e293b);">
            @if ($title)
                <h2 class="mb-4 text-lg font-bold">{{ $title }}</h2>
            @endif

            {{ $slot }}
        </div>
    </div>
@endif

/**
 * The FOUC-avoiding pre-paint class application happens in an inline
 * <script> at the very top of each layout's <head> (must run before any
 * stylesheet paints, so it can't wait for this bundle to load) — this file
 * wires up everything that can safely run after load: the toggle button,
 * telling the rest of the app (e.g. ApexCharts) that the theme just
 * changed, and re-resolving the theme after a Livewire SPA navigation.
 *
 * That last part is required, not cosmetic: `redirectRoute(..., navigate:
 * true)` (used by the login flow) and any `wire:navigate` link fetch the
 * destination page and morph it in — Livewire syncs <html>'s attributes to
 * match the freshly-fetched (server-rendered) markup, which has no idea
 * about the `dark` class a previous inline script added client-side, so it
 * gets silently wiped on every SPA transition unless reapplied here.
 */
function resolveAndApplyTheme() {
    const stored = localStorage.getItem('theme');
    const mode = stored || document.documentElement.dataset.defaultTheme || 'user_choice';
    const dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', dark);
}

window.toggleTheme = function toggleTheme() {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    localStorage.setItem('theme', next);
    document.documentElement.classList.toggle('dark', next === 'dark');
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: next === 'dark' } }));
};

document.addEventListener('livewire:navigated', resolveAndApplyTheme);

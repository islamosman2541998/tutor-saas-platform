@props(['settings'])

<style>
    :root {
        @foreach ($settings->cssVariables() as $name => $value)
            {{ $name }}: {{ $value }};
        @endforeach
    }

    html { scroll-behavior: smooth; }

    body {
        font-family: var(--site-font), sans-serif;
        background: var(--site-bg);
        color: var(--site-text);
        font-size: var(--site-font-size);
        overflow-x: hidden;
    }

    h1, h2, h3 { color: var(--site-heading); }
    .site-hero-title { color: inherit; }

    ::selection { background: color-mix(in srgb, var(--site-primary) 30%, transparent); }

    .site-btn {
        background: var(--site-button);
        border-radius: {{ $settings->buttonRadiusClass() }};
        transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        box-shadow: 0 8px 20px -8px color-mix(in srgb, var(--site-button) 60%, transparent);
    }
    .site-btn:hover {
        background: var(--site-button-hover);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px color-mix(in srgb, var(--site-button) 70%, transparent);
    }
    .site-btn:active { transform: translateY(0); }

    .site-btn-outline {
        border: 1.5px solid color-mix(in srgb, var(--site-primary) 45%, transparent);
        border-radius: {{ $settings->buttonRadiusClass() }};
        transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease;
    }
    .site-btn-outline:hover {
        background: color-mix(in srgb, var(--site-primary) 8%, transparent);
        border-color: var(--site-primary);
        transform: translateY(-2px);
    }

    /* Scroll-reveal: JS toggles .is-revealed once an element enters the
       viewport (see resources/js/website.js) — a no-JS fallback of "always
       visible" is intentional here (base state has no visibility:hidden),
       so content never disappears if JS fails to load. */
    [data-reveal] {
        opacity: 0;
        transform: translateY(34px);
        transition: opacity 1200ms ease, transform 1200ms ease;
    }
    [data-reveal].is-revealed { opacity: 1; transform: translateY(0); }
    [data-reveal="fade"] { transform: none; }
    [data-reveal="left"] { transform: translateX(-60px); }
    [data-reveal="left"].is-revealed { transform: translateX(0); }
    [data-reveal="right"] { transform: translateX(60px); }
    [data-reveal="right"].is-revealed { transform: translateX(0); }
    [data-reveal-delay="1"] { transition-delay: 150ms; }
    [data-reveal-delay="2"] { transition-delay: 300ms; }
    [data-reveal-delay="3"] { transition-delay: 450ms; }
    [data-reveal-delay="4"] { transition-delay: 600ms; }
    [data-reveal-delay="5"] { transition-delay: 750ms; }

    .site-card {
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .site-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -20px rgba(15, 23, 42, 0.25);
    }

    [data-site-header] {
        transition: box-shadow 220ms ease, background-color 220ms ease;
    }
    [data-site-header].is-scrolled {
        box-shadow: 0 6px 24px -12px rgba(15, 23, 42, 0.25);
    }

    [data-nav-menu] {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: max-height 260ms ease, opacity 220ms ease;
    }
    [data-nav-menu].is-open { max-height: 480px; opacity: 1; }
    @media (min-width: 768px) {
        [data-nav-menu] { max-height: none; opacity: 1; overflow: visible; }
    }

    .site-nav-link { position: relative; transition: color 180ms ease; }
    .site-nav-link::after {
        content: '';
        position: absolute;
        right: 0;
        bottom: -4px;
        width: 0;
        height: 2px;
        background: var(--site-primary);
        transition: width 220ms ease;
    }
    .site-nav-link:hover::after { width: 100%; }
    .site-nav-link:hover { color: var(--site-primary); }

    .site-chip {
        transition: background-color 200ms ease, color 200ms ease, transform 200ms ease;
    }
    .site-chip:hover {
        background: var(--site-primary);
        color: #fff;
        transform: translateY(-2px);
    }

    .site-social-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 9999px;
        transition: transform 200ms ease, background-color 200ms ease;
    }
    .site-social-btn:hover { transform: translateY(-3px); }

    /* Hero carousel: internal geometry is deliberately forced to LTR
       regardless of the page's dir="rtl" — translateX(-n * 100%) math for
       "next slide" only stays simple/predictable in one direction, and the
       carousel has no text of its own to mis-align (each slide's own text
       block sets its own dir). */
    .site-hero { position: relative; height: 100vh; height: 100dvh; }
    .site-hero-track { direction: ltr; transition: transform 600ms cubic-bezier(.4,0,.2,1); touch-action: pan-y; cursor: grab; }
    .site-hero-track:active { cursor: grabbing; }
    .site-hero-slide { width: 100%; }

    /* Ken Burns: a slow, continuous zoom on the active slide's image only —
       toggled by initHeroSlider() adding/removing .is-active per slide (see
       website.js). Both ancestors already clip overflow (.site-hero and its
       inner track wrapper), so the zoom crops cleanly with no extra markup. */
    .site-hero-slide img {
        transform: scale(1);
        transition: transform 8s linear;
        will-change: transform;
    }
    .site-hero-slide.is-active img { transform: scale(1.08); }

    .site-slider-arrow {
        position: absolute;
        top: 50%;
        z-index: 20;
        display: flex;
        height: 2.75rem;
        width: 2.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        backdrop-filter: blur(6px);
        transform: translateY(-50%);
        transition: background-color 200ms ease, transform 200ms ease;
    }
    .site-slider-arrow:hover { background: rgba(255, 255, 255, 0.35); transform: translateY(-50%) scale(1.08); }
    .site-slider-arrow-prev { left: .75rem; }
    .site-slider-arrow-next { right: .75rem; }
    @media (min-width: 640px) {
        .site-slider-arrow-prev { left: 1.5rem; }
        .site-slider-arrow-next { right: 1.5rem; }
    }

    .site-slider-dot {
        height: 8px;
        width: 8px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.45);
        transition: background-color 200ms ease, width 200ms ease;
    }
    .site-slider-dot.is-active { width: 22px; background: #fff; }

    .site-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 50;
        background: rgba(15, 23, 42, 0.55);
        opacity: 0;
        visibility: hidden;
        transition: opacity 220ms ease, visibility 220ms ease;
    }
    .site-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 51;
        width: min(92vw, 26rem);
        max-height: 88vh;
        overflow-y: auto;
        padding: 1.75rem;
        background: var(--site-bg, #fff);
        color: var(--site-text, #1e293b);
        border-radius: var(--site-radius, 16px);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.4);
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, -50%) scale(0.95);
        transition: opacity 220ms ease, transform 220ms ease, visibility 220ms ease;
    }
    .site-modal-overlay.is-open,
    .site-modal.is-open {
        opacity: 1;
        visibility: visible;
    }
    .site-modal.is-open { transform: translate(-50%, -50%) scale(1); }
    .site-modal-close {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        color: inherit;
        opacity: 0.6;
        transition: opacity 180ms ease, background-color 180ms ease;
    }
    .site-modal-close:hover { opacity: 1; background: color-mix(in srgb, currentColor 10%, transparent); }

    @keyframes site-float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-14px) rotate(3deg); }
    }
    .site-float { animation: site-float 7s ease-in-out infinite; }
    .site-float-slow { animation: site-float 10s ease-in-out infinite; animation-delay: 1.2s; }

    @keyframes site-pulse-ring {
        0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--site-primary) 35%, transparent); }
        100% { box-shadow: 0 0 0 18px color-mix(in srgb, var(--site-primary) 0%, transparent); }
    }
    .site-pulse-ring { animation: site-pulse-ring 2.4s ease-out infinite; }

    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        [data-reveal] { opacity: 1 !important; transform: none !important; transition: none !important; }
        .site-float, .site-float-slow, .site-pulse-ring { animation: none !important; }
        .site-hero-track { transition: none !important; }
        .site-hero-slide img { transition: none !important; }
        .site-modal, .site-modal-overlay { transition: none !important; }
    }
</style>

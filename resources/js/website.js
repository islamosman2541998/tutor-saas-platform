/**
 * Public marketing-site behaviour only (mobile nav, scroll-reveal, animated
 * counters). Deliberately vanilla JS with no framework — these pages are
 * plain Blade views (not Livewire components), so pulling in Alpine/Livewire
 * here just for this would mean shipping a much heavier runtime than three
 * small DOM behaviours need.
 */

function initMobileNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => menu.classList.remove('is-open'));
    });
}

function initScrollReveal() {
    const targets = document.querySelectorAll('[data-reveal]');

    if (targets.length === 0) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        // Triggers as soon as a sliver of the element enters, with a
        // positive bottom margin extending the trigger zone below the
        // viewport — the reveal transition is intentionally slow (see
        // head-styles.blade.php), so it needs to start early to actually
        // finish playing while the section is still in view.
        { threshold: 0.02, rootMargin: '0px 0px 100px 0px' }
    );

    targets.forEach((el) => observer.observe(el));
}

function initCountUp() {
    const counters = document.querySelectorAll('[data-count-to]');

    if (counters.length === 0) {
        return;
    }

    const animate = (el) => {
        const target = parseInt(el.dataset.countTo, 10) || 0;
        const duration = 1000;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(eased * target).toLocaleString('en-US');

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString('en-US');
            }
        };

        requestAnimationFrame(step);
    };

    if (!('IntersectionObserver' in window)) {
        counters.forEach(animate);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.5 }
    );

    counters.forEach((el) => observer.observe(el));
}

function initHeroSlider() {
    const root = document.querySelector('[data-hero-slider]');
    const track = root?.querySelector('[data-slider-track]');
    const slides = root ? Array.from(root.querySelectorAll('[data-slider-slide]')) : [];

    if (!root || !track || slides.length === 0) {
        return;
    }

    if (slides.length === 1) {
        return; // single slide: no transform/controls/autoplay needed at all
    }

    const AUTOPLAY_MS = 5500;
    const dots = Array.from(root.querySelectorAll('[data-slider-dot]'));
    let index = 0;
    let autoplayTimer = null;
    let dragStartX = 0;
    let dragDeltaX = 0;
    let dragging = false;

    const goTo = (i) => {
        index = ((i % slides.length) + slides.length) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;
        dots.forEach((dot, di) => dot.classList.toggle('is-active', di === index));
        // Drives the Ken Burns zoom (see .site-hero-slide.is-active img in
        // head-styles.blade.php) — only the active slide's image zooms.
        slides.forEach((slide, si) => slide.classList.toggle('is-active', si === index));
    };

    const next = () => goTo(index + 1);
    const prev = () => goTo(index - 1);

    const resetAutoplay = () => {
        clearInterval(autoplayTimer);
        autoplayTimer = setInterval(next, AUTOPLAY_MS);
    };

    root.querySelector('[data-slider-prev]')?.addEventListener('click', () => { prev(); resetAutoplay(); });
    root.querySelector('[data-slider-next]')?.addEventListener('click', () => { next(); resetAutoplay(); });
    dots.forEach((dot, di) => dot.addEventListener('click', () => { goTo(di); resetAutoplay(); }));

    // Drag / swipe (pointer events unify mouse + touch).
    track.addEventListener('pointerdown', (e) => {
        dragging = true;
        dragStartX = e.clientX;
        dragDeltaX = 0;
        track.style.transition = 'none';
        clearInterval(autoplayTimer);
        track.setPointerCapture(e.pointerId);
    });

    track.addEventListener('pointermove', (e) => {
        if (!dragging) {
            return;
        }
        dragDeltaX = e.clientX - dragStartX;
        const percent = (dragDeltaX / root.clientWidth) * 100;
        track.style.transform = `translateX(calc(-${index * 100}% + ${percent}%))`;
    });

    const endDrag = () => {
        if (!dragging) {
            return;
        }
        dragging = false;
        track.style.transition = '';
        const threshold = root.clientWidth * 0.12;

        if (dragDeltaX > threshold) {
            prev();
        } else if (dragDeltaX < -threshold) {
            next();
        } else {
            goTo(index);
        }

        dragDeltaX = 0;
        resetAutoplay();
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    root.addEventListener('mouseenter', () => clearInterval(autoplayTimer));
    root.addEventListener('mouseleave', resetAutoplay);

    goTo(0);
    resetAutoplay();
}

function initStickyHeaderShadow() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

function initRegistrationModal() {
    const overlay = document.querySelector('[data-register-modal-overlay]');
    const modal = document.querySelector('[data-register-modal]');
    const triggers = document.querySelectorAll('[data-register-trigger]');

    if (!overlay || !modal || triggers.length === 0) {
        return;
    }

    const groupNameEl = modal.querySelector('[data-register-modal-group-name]');
    const groupIdInput = modal.querySelector('[data-register-group-id]');
    const form = modal.querySelector('[data-register-form]');
    const successEl = modal.querySelector('[data-register-success]');
    const successMessageEl = modal.querySelector('[data-register-success-message]');
    const genericErrorEl = modal.querySelector('[data-register-generic-error]');
    const submitLabel = modal.querySelector('[data-register-submit-label]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const clearErrors = () => {
        modal.querySelectorAll('[data-error-for]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        genericErrorEl.textContent = '';
        genericErrorEl.classList.add('hidden');
    };

    const open = (groupId, groupName) => {
        form.reset();
        clearErrors();
        form.classList.remove('hidden');
        successEl.classList.add('hidden');
        groupIdInput.value = groupId;
        groupNameEl.textContent = groupName;
        overlay.classList.add('is-open');
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        overlay.classList.remove('is-open');
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => open(trigger.dataset.groupId, trigger.dataset.groupName));
    });

    overlay.addEventListener('click', close);
    modal.querySelector('[data-register-modal-close]')?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            close();
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        submitLabel.textContent = 'جارٍ الإرسال...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.status === 422 && payload.errors) {
                Object.entries(payload.errors).forEach(([field, messages]) => {
                    const el = modal.querySelector(`[data-error-for="${field}"]`);
                    if (el) {
                        el.textContent = messages[0];
                        el.classList.remove('hidden');
                    }
                });
                return;
            }

            if (!response.ok) {
                genericErrorEl.textContent = payload.message || 'حدث خطأ، حاول مرة أخرى.';
                genericErrorEl.classList.remove('hidden');
                return;
            }

            successMessageEl.textContent = payload.message || 'تم إرسال طلبك بنجاح.';
            form.classList.add('hidden');
            successEl.classList.remove('hidden');
        } catch (err) {
            genericErrorEl.textContent = 'تعذّر الاتصال بالخادم، حاول مرة أخرى.';
            genericErrorEl.classList.remove('hidden');
        } finally {
            submitLabel.textContent = 'إرسال الطلب';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initScrollReveal();
    initCountUp();
    initHeroSlider();
    initStickyHeaderShadow();
    initRegistrationModal();
});

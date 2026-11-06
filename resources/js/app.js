import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const backToTop = document.querySelector('[data-back-to-top]');
if (backToTop) {
    let scrollUpdatePending = false;
    const updateBackToTop = () => {
        backToTop.hidden = window.scrollY < 420;
        scrollUpdatePending = false;
    };
    window.addEventListener('scroll', () => {
        if (scrollUpdatePending) return;
        scrollUpdatePending = true;
        window.requestAnimationFrame(updateBackToTop);
    }, { passive: true });
    window.addEventListener('pageshow', updateBackToTop);
    updateBackToTop();
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    });
}

document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-main-nav]')?.classList.toggle('open');
});

document.querySelectorAll('[data-header-search]').forEach(form => {
    const input = form.querySelector('[data-header-search-input]');
    const submit = form.querySelector('[data-header-search-submit]');
    const close = form.querySelector('[data-header-search-close]');
    if (!input || !submit) return;

    const setOpen = (open, restoreFocus = false) => {
        form.classList.toggle('is-open', open);
        form.setAttribute('aria-expanded', String(open));
        submit.setAttribute('aria-label', open ? 'Search products' : 'Open search');
        if (open) requestAnimationFrame(() => input.focus());
        else if (restoreFocus) submit.focus();
    };

    form.addEventListener('submit', event => {
        if (!input.value.trim()) {
            event.preventDefault();
            setOpen(true);
        }
    });
    close?.addEventListener('click', () => setOpen(false, true));
    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            event.preventDefault();
            setOpen(false, true);
        }
    });
    document.addEventListener('click', event => {
        if (form.classList.contains('is-open') && !form.contains(event.target)) setOpen(false);
    });
});

document.querySelectorAll('.nav-category').forEach(category => {
    const summary = category.querySelector('summary');
    const closeButton = category.querySelector('[data-menu-close]');
    closeButton?.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        category.removeAttribute('open');
        summary?.focus();
    });
    document.addEventListener('click', event => {
        if (category.open && !category.contains(event.target)) category.removeAttribute('open');
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && category.open) {
            category.removeAttribute('open');
            summary?.focus();
        }
    });
});

document.querySelectorAll('[data-cart-toggle]').forEach(toggle => {
    const dropdown = document.getElementById(toggle.getAttribute('aria-controls'));
    const wrapper = toggle.closest('.header-cart');
    if (!dropdown || !wrapper) return;
    const close = (restoreFocus = false) => {
        dropdown.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', toggle.getAttribute('aria-label').replace('Close shopping bag', 'Open shopping bag'));
        if (restoreFocus) toggle.focus();
    };
    toggle.addEventListener('click', event => {
        event.stopPropagation();
        const open = dropdown.hidden;
        dropdown.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', toggle.getAttribute('aria-label').replace(open ? 'Open shopping bag' : 'Close shopping bag', open ? 'Close shopping bag' : 'Open shopping bag'));
    });
    document.addEventListener('click', event => {
        if (!wrapper.contains(event.target) && !dropdown.hidden) close();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !dropdown.hidden) close(true);
    });
});

document.querySelectorAll('[data-scroll-rail]').forEach(button => {
    button.addEventListener('click', () => {
        const rail = document.getElementById(button.dataset.scrollRail);
        const direction = Number(button.dataset.scrollBy || 1);
        if (rail) rail.scrollBy({ left: rail.clientWidth * 0.82 * direction, behavior: 'smooth' });
    });
});

document.querySelectorAll('[data-gallery-image]').forEach(button => {
    button.addEventListener('click', () => {
        const image = document.querySelector('#product-gallery-image');
        if (!image) return;
        image.src = button.dataset.galleryImage;
        document.querySelectorAll('[data-gallery-image]').forEach(item => {
            const active = item === button;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', String(active));
        });
    });
});

const updateConsent = document.querySelector('#footer-update-consent');
document.querySelector('#footer-email-updates')?.addEventListener('submit', event => {
    event.preventDefault();
    if (!event.currentTarget.reportValidity() || !updateConsent?.reportValidity()) return;
    const email = new FormData(event.currentTarget).get('email');
    const body = `Please add ${email} to Crystal Car Audio product updates and offers.`;
    window.location.href = `mailto:info@crystalcaraudio.com?subject=${encodeURIComponent('Crystal Car Audio product updates')}&body=${encodeURIComponent(body)}`;
});

document.querySelector('#footer-whatsapp-updates')?.addEventListener('submit', event => {
    event.preventDefault();
    if (!event.currentTarget.reportValidity() || !updateConsent?.reportValidity()) return;
    const phone = new FormData(event.currentTarget).get('phone');
    const message = `Please add my WhatsApp number ${phone} to Crystal Car Audio product updates and offers.`;
    window.location.href = `https://wa.me/254790721397?text=${encodeURIComponent(message)}`;
});

if ('IntersectionObserver' in window) {
    const revealTargets = document.querySelectorAll([
        'main .section:not(.checkout-layout):not(.cart-layout)',
        'main > .service-strip',
        'main > .feature-banner',
        'main > .product-collection',
        'main > .brand-collection',
        'main > .why-crystal',
        'main > .expert-cta',
        'main > .listing-storefront',
        'main > .product-detail-page',
        'main > .product-information',
        'main > .product-reviews',
        'main > .customer-dashboard',
    ].join(', '));
    revealTargets.forEach(element => element.classList.add('scroll-in'));
    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.04, rootMargin: '0px 0px -36px 0px' });
    revealTargets.forEach(element => revealObserver.observe(element));
}

const session = window.orderCheckoutSession;
if (session?.checkout_url) {
    const frame = document.querySelector('#econfirm-frame');
    const dialog = document.querySelector('#escrow-dialog');
    const status = document.querySelector('#checkout-status');
    const origin = new URL(session.checkout_url).origin;
    let reconciling = false;
    const close = () => { dialog.hidden = true; frame.src = 'about:blank'; };
    document.querySelector('#open-checkout')?.addEventListener('click', () => {
        frame.src = `${session.checkout_url}#secret=${encodeURIComponent(session.checkout_secret)}`;
        dialog.hidden = false;
    });
    if (new URLSearchParams(window.location.search).get('checkout_return') === '1') {
        frame.src = `${session.checkout_url}#secret=${encodeURIComponent(session.checkout_secret)}`;
        dialog.hidden = false;
        void reconcile();
    }
    document.querySelector('#close-checkout')?.addEventListener('click', close);
    async function reconcile() {
        if (reconciling) return;
        reconciling = true;
        status.textContent = 'Payment received. Confirming your order securely…';
        let delay = 1500;
        while (reconciling) {
            try {
                const response = await fetch(window.orderCheckoutReconcileUrl, { method: 'POST', credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
                if (response.ok) {
                    const result = await response.json();
                    if (result.status === 'funded') { window.location.assign(window.orderCompleteUrl); return; }
                }
            } catch (_) {}
            await new Promise(resolve => setTimeout(resolve, delay));
            delay = Math.min(Math.round(delay * 1.5), 8000);
        }
    }
    window.addEventListener('message', event => {
        if (event.origin !== origin || event.source !== frame.contentWindow || event.data?.source !== 'econfirm') return;
        if (event.data.event === 'checkout.closed') close();
        if ((event.data.event === 'checkout.completed' || event.data.event === 'checkout.return') && event.data.status === 'funded') void reconcile();
    });
}

const completePage = document.querySelector('.complete-page');
if (completePage?.dataset.reconcileUrl) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let delay = 1500;
    const verify = async () => {
        try {
            const response = await fetch(completePage.dataset.reconcileUrl, { method: 'POST', credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
            if (response.ok && (await response.json()).status === 'funded') { window.location.assign(completePage.dataset.completeUrl); return; }
        } catch (_) {}
        window.setTimeout(verify, delay);
        delay = Math.min(Math.round(delay * 1.5), 8000);
    };
    void verify();
}

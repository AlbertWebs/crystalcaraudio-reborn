import Alpine from 'alpinejs';

window.Alpine = Alpine;

const prepareStoreImage = image => {
    if (!(image instanceof HTMLImageElement) || image.closest('noscript')) return;
    if (image.getAttribute('width') === '1' && image.getAttribute('height') === '1') return;

    if (!image.hasAttribute('loading')) {
        image.loading = image.fetchPriority === 'high' ? 'eager' : 'lazy';
    }
    if (!image.hasAttribute('decoding')) image.decoding = 'async';

    image.classList.remove('image-is-loaded', 'image-is-error');
    const sourceAtStart = image.currentSrc || image.src;
    const markLoaded = () => {
        if ((image.currentSrc || image.src) !== sourceAtStart) return;
        image.classList.remove('image-is-loading', 'image-is-error');
        image.classList.add('image-is-loaded');
    };
    const markError = () => {
        if ((image.currentSrc || image.src) !== sourceAtStart) return;
        image.classList.remove('image-is-loading', 'image-is-loaded');
        image.classList.add('image-is-error');
    };

    image.classList.add('image-is-loading');
    image.addEventListener('load', markLoaded, { once: true });
    image.addEventListener('error', markError, { once: true });
    if (image.complete) {
        if (image.naturalWidth > 0) markLoaded();
        else markError();
    }
};

const prepareStoreImages = root => {
    if (root instanceof HTMLImageElement) prepareStoreImage(root);
    root.querySelectorAll?.('img').forEach(prepareStoreImage);
};

const initializeStoreImages = () => {
    const store = document.querySelector('.storefront-site');
    if (!store) return;

    prepareStoreImages(store);
    const imageObserver = new MutationObserver(changes => {
        changes.forEach(change => {
            if (change.type === 'attributes') {
                prepareStoreImage(change.target);
                return;
            }
            change.addedNodes.forEach(node => {
                if (node instanceof Element) prepareStoreImages(node);
            });
        });
    });
    imageObserver.observe(store, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeStoreImages, { once: true });
} else {
    initializeStoreImages();
}

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
    summary?.addEventListener('click', event => {
        if (!window.matchMedia('(max-width: 760px)').matches || !summary.dataset.mobileCategoryUrl) return;
        event.preventDefault();
        window.location.assign(summary.dataset.mobileCategoryUrl);
    });
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

const cartToastRegion = document.querySelector('[data-cart-toast-region]');
let cartToastTimer;
const showCartToast = ({ success, productName = '', productImage = '', message = '' }) => {
    if (!cartToastRegion) return;
    window.clearTimeout(cartToastTimer);
    const toast = document.createElement('section');
    toast.className = `cart-toast${success ? ' is-success' : ' is-error'}`;
    toast.setAttribute('role', 'status');

    const icon = document.createElement('span');
    icon.className = 'cart-toast-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = success
        ? '<svg viewBox="0 0 24 24"><path d="m5 12.5 4.2 4.2L19 7"/></svg>'
        : '<svg viewBox="0 0 24 24"><path d="M12 8v5m0 3h.01M12 3 2.8 20h18.4L12 3Z"/></svg>';
    toast.append(icon);

    if (success && productImage) {
        toast.classList.add('has-product-image');
        const image = document.createElement('img');
        image.className = 'cart-toast-product-image';
        image.src = productImage;
        image.alt = '';
        toast.append(image);
    }

    const copy = document.createElement('div');
    copy.className = 'cart-toast-copy';
    const heading = document.createElement('strong');
    heading.textContent = success ? 'Added to your bag' : 'Could not add this item';
    const detail = document.createElement('span');
    detail.textContent = success ? productName : (message || 'Please try again in a moment.');
    copy.append(heading, detail);
    if (success) {
        const viewBag = document.createElement('a');
        viewBag.href = '/shopping-cart';
        viewBag.textContent = 'View your bag';
        copy.append(viewBag);
    }
    toast.append(copy);

    const dismiss = document.createElement('button');
    dismiss.className = 'cart-toast-dismiss';
    dismiss.type = 'button';
    dismiss.setAttribute('aria-label', 'Dismiss message');
    dismiss.textContent = '×';
    dismiss.addEventListener('click', () => toast.remove());
    toast.append(dismiss);

    cartToastRegion.replaceChildren(toast);
    requestAnimationFrame(() => toast.classList.add('is-visible'));
    cartToastTimer = window.setTimeout(() => {
        toast.classList.remove('is-visible');
        window.setTimeout(() => toast.remove(), 240);
    }, 5200);
};

document.querySelectorAll('.card-add-form, #detail-purchase-form').forEach(form => {
    form.addEventListener('submit', async event => {
        const submitter = event.submitter;
        if (submitter?.name === 'buy_now' && submitter.value === '1') return;
        event.preventDefault();

        const button = submitter || form.querySelector('button[type="submit"]') || document.querySelector('[form="detail-purchase-form"][name="buy_now"][value="0"]');
        if (button?.disabled) return;
        const originalContent = button?.innerHTML;
        const wasDisabled = button?.disabled ?? false;
        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('is-adding-to-bag');
        }

        try {
            const formData = new FormData(form);
            if (submitter?.name) formData.set(submitter.name, submitter.value);
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'Please try again in a moment.');

            const count = document.querySelector('[data-header-cart-count]');
            const cartToggle = document.querySelector('[data-cart-toggle]');
            const miniCart = document.querySelector('[data-mini-cart-content]');
            if (count) count.textContent = result.cart_count;
            if (cartToggle) {
                const state = cartToggle.getAttribute('aria-expanded') === 'true' ? 'Close' : 'Open';
                cartToggle.setAttribute('aria-label', `${state} shopping bag, ${result.cart_count} items`);
                cartToggle.classList.remove('is-bumped');
                void cartToggle.offsetWidth;
                cartToggle.classList.add('is-bumped');
                window.setTimeout(() => cartToggle.classList.remove('is-bumped'), 650);
            }
            if (miniCart && result.mini_cart_html) miniCart.innerHTML = result.mini_cart_html;
            showCartToast({ success: true, productName: result.product_name, productImage: result.product_image });
        } catch (error) {
            showCartToast({ success: false, message: error.message });
        } finally {
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = wasDisabled;
                button.removeAttribute('aria-busy');
                button.classList.remove('is-adding-to-bag');
            }
        }
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
        const gallery = button.closest('.product-gallery');
        const image = gallery?.querySelector('#product-gallery-image');
        if (!image) return;
        image.src = button.dataset.galleryImage;
        const thumbnailButtons = gallery.querySelectorAll('[data-gallery-image]');
        thumbnailButtons.forEach(item => {
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

const productEnquiryDialog = document.querySelector('#product-enquiry-dialog');
const productEnquiryForm = productEnquiryDialog?.querySelector('[data-product-enquiry-form]');
document.querySelector('[data-open-product-enquiry]')?.addEventListener('click', () => {
    if (!productEnquiryDialog?.showModal) return;
    productEnquiryDialog.showModal();
    productEnquiryDialog.querySelector('input[name="name"]')?.focus();
});
productEnquiryDialog?.querySelector('[data-close-product-enquiry]')?.addEventListener('click', () => productEnquiryDialog.close());
productEnquiryDialog?.addEventListener('click', event => {
    if (event.target === productEnquiryDialog) productEnquiryDialog.close();
});
productEnquiryForm?.addEventListener('submit', event => {
    event.preventDefault();
    if (!productEnquiryForm.reportValidity()) return;

    const values = new FormData(productEnquiryForm);
    const message = [
        `Hello Crystal Car Audio, my name is ${values.get('name')}.`,
        `I’m enquiring about ${productEnquiryForm.dataset.productName}.`,
        `Product page: ${productEnquiryForm.dataset.productUrl}`,
        `My phone number: ${values.get('phone')}`,
        `My question: ${values.get('message')}`,
    ].join('\n\n');
    const whatsappUrl = `https://wa.me/254790721397?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
    productEnquiryDialog.close();
});

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const scrollProgress = document.createElement('div');
scrollProgress.className = 'scroll-progress';
scrollProgress.setAttribute('aria-hidden', 'true');
document.body.prepend(scrollProgress);

const parallaxTargets = reduceMotion ? [] : [...document.querySelectorAll([
    '.hero-combo-art .hero-orbit',
    '.hero-combo-art .sun',
    '.hero-combo-art .hero-art-index',
    '.feature-banner-art .feature-ring',
    '.expert-visual',
    '.premium-audio-art-orbit',
].join(', '))];
parallaxTargets.forEach(element => element.classList.add('scroll-parallax'));

let scrollFramePending = false;
const updateScrollEffects = () => {
    const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = scrollableHeight > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollableHeight)) : 0;
    scrollProgress.style.transform = `scaleX(${progress})`;

    if (!reduceMotion && window.innerWidth > 760) {
        parallaxTargets.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > window.innerHeight) return;
            const center = rect.top + rect.height / 2;
            const offset = Math.max(-18, Math.min(18, (0.5 - center / window.innerHeight) * 28));
            element.style.setProperty('--scroll-shift', `${offset.toFixed(1)}px`);
        });
    }
    scrollFramePending = false;
};
const requestScrollUpdate = () => {
    if (scrollFramePending) return;
    scrollFramePending = true;
    window.requestAnimationFrame(updateScrollEffects);
};
window.addEventListener('scroll', requestScrollUpdate, { passive: true });
window.addEventListener('resize', requestScrollUpdate, { passive: true });
window.addEventListener('pageshow', requestScrollUpdate);
requestScrollUpdate();

if (!reduceMotion && 'IntersectionObserver' in window) {
    const revealTargets = document.querySelectorAll([
        'main > .hero',
        'main > .page-hero',
        'main > .premium-audio-hero',
        'main > .premium-audio-intro',
        'main > .premium-audio-sections > .premium-audio-section',
        'main > .premium-audio-guide',
        'main > .premium-audio-local',
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
        'main > .cart-recommendations',
    ].join(', '));
    const staggerSelector = '.product-card, .category-card, .why-grid article, .service-cards article, .brand-pills > a, .premium-audio-guide-grid article';
    revealTargets.forEach((element, index) => {
        element.classList.add('scroll-in');
        if (index % 3 === 1) element.classList.add('scroll-in-from-left');
        if (index % 3 === 2) element.classList.add('scroll-in-from-right');
        element.querySelectorAll(staggerSelector).forEach((child, childIndex) => {
            child.classList.add('scroll-reveal-child');
            child.style.setProperty('--reveal-delay', `${Math.min(childIndex * 70, 350)}ms`);
        });
    });
    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.025, rootMargin: '0px 0px -42px 0px' });
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

/* Homepage combo slide controls */
document.querySelectorAll('[data-combo-carousel]').forEach(carousel => {
    const slides = [...carousel.querySelectorAll('[data-combo-slide]')];
    const dots = [...(carousel.parentElement?.querySelectorAll('[data-combo-dot]') ?? [])];
    const counter = carousel.parentElement?.querySelector('[data-combo-count]');
    if (slides.length < 2 || dots.length !== slides.length) return;

    let activeIndex = 0;
    let rotationTimer;
    let touchStartX = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const setSlide = index => {
        activeIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            slide.hidden = slideIndex !== activeIndex;
            slide.setAttribute('aria-hidden', String(slideIndex !== activeIndex));
        });
        dots.forEach((dot, dotIndex) => {
            const active = dotIndex === activeIndex;
            dot.setAttribute('aria-selected', String(active));
            if (active) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
        if (counter) counter.textContent = `${String(activeIndex + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
    };
    const stopRotation = () => window.clearInterval(rotationTimer);
    const startRotation = () => {
        stopRotation();
        if (reducedMotion || document.hidden) return;
        rotationTimer = window.setInterval(() => setSlide(activeIndex + 1), 6000);
    };

    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        setSlide(index);
        startRotation();
    }));
    carousel.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        setSlide(activeIndex + (event.key === 'ArrowRight' ? 1 : -1));
        startRotation();
    });
    carousel.addEventListener('mouseenter', stopRotation);
    carousel.addEventListener('mouseleave', startRotation);
    carousel.addEventListener('focusin', stopRotation);
    carousel.addEventListener('focusout', event => {
        if (!carousel.contains(event.relatedTarget)) startRotation();
    });
    carousel.addEventListener('touchstart', event => { touchStartX = event.changedTouches[0]?.clientX ?? null; }, { passive: true });
    carousel.addEventListener('touchend', event => {
        const endX = event.changedTouches[0]?.clientX;
        if (touchStartX === null || endX === undefined || Math.abs(endX - touchStartX) < 45) return;
        setSlide(activeIndex + (endX < touchStartX ? 1 : -1));
        touchStartX = null;
        startRotation();
    }, { passive: true });
    document.addEventListener('visibilitychange', startRotation);
    setSlide(0);
    startRotation();
});

const storeChatPanel = document.querySelector('#store-chat-panel');
const storeChatToggle = document.querySelector('[data-store-chat-toggle]');
const storeChatMessage = document.querySelector('#store-chat-message');
const setStoreChatOpen = isOpen => {
    if (!storeChatPanel || !storeChatToggle) return;
    storeChatPanel.hidden = !isOpen;
    storeChatToggle.classList.toggle('is-open', isOpen);
    storeChatToggle.setAttribute('aria-expanded', String(isOpen));
    storeChatToggle.setAttribute('aria-label', `${isOpen ? 'Close' : 'Open'} WhatsApp chat with Crystal Car Audio`);
    if (isOpen) storeChatMessage?.focus();
};

storeChatToggle?.addEventListener('click', () => setStoreChatOpen(storeChatPanel?.hidden ?? false));
document.querySelector('[data-store-chat-close]')?.addEventListener('click', () => {
    setStoreChatOpen(false);
    storeChatToggle?.focus();
});
storeChatPanel?.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        setStoreChatOpen(false);
        storeChatToggle?.focus();
    }
});
document.querySelector('[data-store-chat-form]')?.addEventListener('submit', event => {
    event.preventDefault();
    const message = storeChatMessage?.value.trim();
    if (!message) return;
    const productName = document.querySelector('.product-purchase-panel h1')?.textContent.trim();
    const opening = productName ? `Hi Crystal Car Audio, I have a question about ${productName}:` : 'Hi Crystal Car Audio, I have a question:';
    const whatsappUrl = `https://wa.me/254790721397?text=${encodeURIComponent(`${opening} ${message}`)}`;
    window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
});

Alpine.start();

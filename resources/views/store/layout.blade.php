<!doctype html>
<html lang="en-KE">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $noIndexRoute = request()->routeIs('cart', 'checkout', 'checkout.*', 'search*');
        $searchFilterKeys = ['q', 'category', 'brand', 'sort', 'min_price', 'max_price'];
        $hasSearchFilters = array_intersect($searchFilterKeys, array_keys(request()->query())) !== [];
        $robotsDirective = $noIndexRoute || $hasSearchFilters ? 'noindex,follow' : 'index,follow';
        $canonicalUrl = url()->current();
        $pageNumber = (int) request()->query('page', 1);
        if (! $noIndexRoute && ! $hasSearchFilters && $pageNumber > 1) {
            $canonicalUrl .= '?page='.$pageNumber;
        }
    @endphp
    <title>@yield('title', 'Crystal Car Audio | Sound that moves you')</title>
    <meta name="description" content="@yield('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')">
    <meta name="robots" content="{{ $robotsDirective }}">
    <meta name="googlebot" content="{{ $robotsDirective }}">
    <meta name="subject" content="Crystal Car Audio - Clear Beats with Premium Car Sound Systems in Nairobi">
    <meta name="rating" content="General">
    <meta name="author" content="Designekta Studios">
    <meta property="fb:app_id" content="431980657174772">
    <!-- Google Tag Manager -->
    <script>
        (function (windowObject, documentObject, scriptTag, dataLayerName, containerId) {
            windowObject[dataLayerName] = windowObject[dataLayerName] || [];
            windowObject[dataLayerName].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            const firstScript = documentObject.getElementsByTagName(scriptTag)[0];
            const tagScript = documentObject.createElement(scriptTag);
            const layerQuery = dataLayerName !== 'dataLayer' ? '&l=' + dataLayerName : '';
            tagScript.async = true;
            tagScript.src = 'https://www.googletagmanager.com/gtm.js?id=' + containerId + layerQuery;
            firstScript.parentNode.insertBefore(tagScript, firstScript);
        })(window, document, 'script', 'dataLayer', 'GTM-WGDFGCZZ');
    </script>
    <!-- End Google Tag Manager -->
    <!-- Meta Pixel -->
    <script>
        !function (windowObject, documentObject, scriptTag, sourceUrl, pixelFunction, scriptElement, firstScript) {
            if (windowObject.fbq) return;
            pixelFunction = windowObject.fbq = function () {
                pixelFunction.callMethod ? pixelFunction.callMethod.apply(pixelFunction, arguments) : pixelFunction.queue.push(arguments);
            };
            if (!windowObject._fbq) windowObject._fbq = pixelFunction;
            pixelFunction.push = pixelFunction;
            pixelFunction.loaded = true;
            pixelFunction.version = '2.0';
            pixelFunction.queue = [];
            scriptElement = documentObject.createElement(scriptTag);
            scriptElement.async = true;
            scriptElement.src = sourceUrl;
            firstScript = documentObject.getElementsByTagName(scriptTag)[0];
            firstScript.parentNode.insertBefore(scriptElement, firstScript);
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1672338073611112');
        fbq('track', 'PageView');
    </script>
    <!-- End Meta Pixel -->
    <meta name="keywords" content="@yield('meta_keywords', 'car audio Kenya, car radios Nairobi, car speakers, car accessories, Crystal Car Audio')">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="geo.region" content="KE-30">
    <meta name="geo.placename" content="Nairobi">
    <link rel="icon" href="{{ \App\Support\MediaUrl::url('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ \App\Support\MediaUrl::url('favicon/favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ \App\Support\MediaUrl::url('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ \App\Support\MediaUrl::url('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ \App\Support\MediaUrl::url('favicon/apple-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('favicon/manifest.json') }}">
    <meta name="theme-color" content="#1c2c52">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Crystal Car Audio">
    <meta name="application-name" content="Crystal Car Audio">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="true">
    <meta name="format-detection" content="telephone=yes">
    <meta name="referrer" content="no-referrer">
    <meta name="msapplication-config" content="{{ asset('favicon/browserconfig.xml') }}">
    <meta property="og:site_name" content="Crystal Car Audio">
    <meta property="og:type" content="@yield('meta_og_type', 'website')">
    <meta property="og:title" content="@yield('meta_og_title', trim($__env->yieldContent('title', 'Crystal Car Audio | Sound that moves you')))" >
    <meta property="og:description" content="@yield('meta_og_description', trim($__env->yieldContent('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')))" >
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('meta_image', \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png'))">
    @hasSection('meta_product_price')<meta property="product:price:amount" content="@yield('meta_product_price')">@endif
    @hasSection('meta_product_currency')<meta property="product:price:currency" content="@yield('meta_product_currency')">@endif
    @hasSection('meta_product_availability')<meta property="product:availability" content="@yield('meta_product_availability')">@endif
    @hasSection('meta_product_brand')<meta property="product:brand" content="@yield('meta_product_brand')">@endif
    @hasSection('meta_product_sku')<meta property="product:retailer_item_id" content="@yield('meta_product_sku')">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="@yield('meta_image', \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png'))">
    <meta name="twitter:title" content="@yield('meta_og_title', trim($__env->yieldContent('title', 'Crystal Car Audio | Sound that moves you')))" >
    <meta name="twitter:description" content="@yield('meta_og_description', trim($__env->yieldContent('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')))" >
    @php
        $siteUrl = rtrim(url('/'), '/');
        $logoUrl = \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png');
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $siteUrl.'/#organization',
                    'name' => 'Crystal Car Audio',
                    'url' => $siteUrl.'/',
                    'logo' => $logoUrl,
                    'sameAs' => [
                        'https://www.facebook.com/Crystalclearbeats/',
                        'https://www.instagram.com/crys_talcaraudio/',
                        'https://ke.linkedin.com/company/crystal-car-audio',
                    ],
                    'telephone' => '+254790721397',
                    'email' => 'info@crystalcaraudio.com',
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+254790721397',
                        'email' => 'info@crystalcaraudio.com',
                        'contactType' => 'customer service',
                        'areaServed' => 'KE',
                        'availableLanguage' => ['English', 'Swahili'],
                    ],
                ],
                [
                    '@type' => 'AutoPartsStore',
                    '@id' => $siteUrl.'/#localbusiness',
                    'name' => 'Crystal Car Audio',
                    'url' => $siteUrl.'/',
                    'image' => $logoUrl,
                    'telephone' => '+254790721397',
                    'email' => 'info@crystalcaraudio.com',
                    'parentOrganization' => ['@id' => $siteUrl.'/#organization'],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'Roasters, Garden Estate Road, opposite MCK Ruaraka Church, next to Big Fish',
                        'addressLocality' => 'Nairobi',
                        'addressRegion' => 'Nairobi County',
                        'addressCountry' => 'KE',
                    ],
                    'hasMap' => 'https://www.google.com/maps/place/Crystal+Car+Audio/data=!4m2!3m1!1s0x0:0x69ba04f279949126',
                    'areaServed' => ['Nairobi', 'Kenya'],
                    'openingHoursSpecification' => [
                        [
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                            'opens' => '08:00',
                            'closes' => '18:00',
                        ],
                        [
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => 'Sunday',
                            'opens' => '09:00',
                            'closes' => '13:00',
                        ],
                    ],
                    'currenciesAccepted' => 'KES',
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $siteUrl.'/#website',
                    'url' => $siteUrl.'/',
                    'name' => 'Crystal Car Audio',
                    'description' => 'Car audio systems, car radios, speakers and professional installation in Nairobi, Kenya.',
                    'inLanguage' => 'en-KE',
                    'publisher' => ['@id' => $siteUrl.'/#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/products').'?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($organizationSchema)</script>
    @stack('structured-data')
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="storefront-site @yield('body_class')">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WGDFGCZZ" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1672338073611112&amp;ev=PageView&amp;noscript=1" alt=""></noscript>
@php
    $headerCategories = \Illuminate\Support\Facades\DB::table('catalog_categories')->orderByRaw('CASE WHEN sort_order = 0 THEN 1 ELSE 0 END')->orderBy('sort_order')->orderBy('name')->get(['name','slug']);
    $headerCategories = $headerCategories->reject(fn ($category) => in_array($category->slug, ['car-audio-system', 'car-audio-systems'], true));
    $headerAudioCategories = $headerCategories->filter(function ($category) {
        foreach (['audio', 'radio', 'speaker', 'amplifier', 'equalizer'] as $term) {
            if (str_contains(strtolower($category->name), $term)) return true;
        }
        return false;
    })->take(5)->values();
    $menuProducts = \App\Models\CatalogProduct::query()->whereNotNull('image')->where('image', '!=', '')->orderByDesc('featured')->orderByDesc('trending')->latest()->take(9)->get(['name','slug','price','image','category','featured','trending','is_offer','is_special']);
    $featuredMenuProduct = $menuProducts->first(function ($item) {
        return in_array(strtolower((string) $item->category), ['car audio system', 'car radio', 'car speakers'], true);
    }) ?? $menuProducts->first();
    $headerCartItems = collect(session('cart', []));
    $headerCartCount = $headerCartItems->sum('quantity');
    $headerCartSubtotal = $headerCartItems->sum(fn ($item) => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0));
@endphp
<header class="site-header">
    <div class="header-topline"><a href="{{ route('home') }}">Crystal Car Audio <span>|</span> Nairobi</a><p>Kenya's Trusted Car Audio Dealer for Premium Sound Systems &amp; Accessories</p><a class="topline-contact" href="tel:+254790721397">Call +254 790 721 397</a></div>
    <div class="header-main">
        <a class="brand" href="{{ route('home') }}" aria-label="Crystal Car Audio home"><img class="brand-logo" src="{{ \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png') }}" alt="Crystal Car Audio" loading="eager" fetchpriority="high" decoding="async"></a>
        <button class="menu-toggle" aria-label="Open navigation" data-menu-toggle aria-expanded="false">☰</button>
        <nav class="main-nav" data-main-nav aria-label="Main navigation">
            <a href="{{ route('categories') }}">Shop all</a>
            <details class="nav-category">
                <summary data-mobile-category-url="{{ $headerAudioCategories->first() ? route('product-category', $headerAudioCategories->first()->slug) : route('categories') }}">{{ $headerAudioCategories->first()->name ?? 'Car audio' }} <span>⌄</span></summary>
                <div class="category-mega">
                    <div class="mega-category-list"><p class="mega-heading">SHOP BY CATEGORY</p>@foreach($headerAudioCategories as $category)<a href="{{ $category->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $category->slug) }}" @if($loop->first) class="is-active" @endif><i aria-hidden="true">{{ str_contains(strtolower($category->name), 'speaker') ? '◖' : (str_contains(strtolower($category->name), 'radio') ? '▤' : (str_contains(strtolower($category->name), 'amplifier') ? '◈' : '♫')) }}</i><span class="mega-category-name">{{ $category->name }}</span><b aria-hidden="true">›</b></a>@endforeach<a class="mega-all" href="{{ route('shop-by-category') }}"><i aria-hidden="true">↗</i><span class="mega-category-name">View all categories</span></a></div>
                    @if($featuredMenuProduct)<a class="mega-feature-product" href="{{ route('product-single', $featuredMenuProduct->slug) }}"><span class="mega-feature-image"><img loading="lazy" decoding="async" src="{{ str_starts_with($featuredMenuProduct->image, 'http') ? $featuredMenuProduct->image : \App\Support\MediaUrl::url('uploads/'.$featuredMenuProduct->image) }}" alt="{{ $featuredMenuProduct->name }} product image"></span><small>FEATURED PICK</small><strong>{{ \Illuminate\Support\Str::limit($featuredMenuProduct->name, 38) }}</strong><b>KES {{ number_format($featuredMenuProduct->price) }}</b></a>@endif
                    <div class="mega-product-area"><div class="mega-product-heading"><div><p class="eyebrow">CRYSTAL PICKS</p><h3>Popular upgrades</h3></div><div class="mega-heading-actions"><a href="{{ route('categories') }}">Shop all <span>↗</span></a><button class="mega-menu-close" type="button" data-menu-close aria-label="Close category menu">×</button></div></div><div class="mega-product-grid">@foreach($menuProducts->reject(fn($item) => $featuredMenuProduct && $item->slug === $featuredMenuProduct->slug) as $menuProduct)<a class="mega-product" href="{{ route('product-single', $menuProduct->slug) }}">@if($menuProduct->featured || $menuProduct->trending)<small class="mega-product-badge">New Arrival</small>@endif<span class="mega-product-image"><img loading="lazy" decoding="async" src="{{ str_starts_with($menuProduct->image, 'http') ? $menuProduct->image : \App\Support\MediaUrl::url('uploads/'.$menuProduct->image) }}" alt="{{ $menuProduct->name }} product image"></span><span class="mega-product-name">{{ \Illuminate\Support\Str::limit($menuProduct->name, 42) }}</span><strong>KES {{ number_format($menuProduct->price) }}</strong></a>@endforeach</div></div>
                    <aside class="mega-service-panel"><p class="eyebrow">CRYSTAL CAR AUDIO</p><h3>Expert help for your next upgrade</h3><div class="mega-service-perks"><span><i>◉</i> Product advice</span><span><i>⌁</i> Fitment support</span><span><i>↗</i> Kenya wide delivery</span><span><i>▣</i> Secure checkout</span></div><div class="mega-service-actions"><a href="https://wa.me/254790721397">WhatsApp our team <span>↗</span></a><a href="tel:+254790721397">Call Crystal <span>↗</span></a></div></aside>
                </div>
            </details>
            @foreach($headerAudioCategories->skip(1) as $category)
                <a href="{{ $category->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $category->slug) }}">{{ $category->name }}</a>
            @endforeach
            <a href="{{ route('car-audio-systems') }}">Car Audio System</a>
            <a href="{{ route('special-offers') }}">Offers</a>
        </nav>
        <div class="header-actions">
            <form class="search" action="{{ route('search-results') }}" role="search" data-header-search aria-expanded="false"><label class="sr-only" for="header-search-input">Search Crystal Car Audio products</label><input id="header-search-input" name="q" value="{{ request('q') }}" placeholder="Search car radios, speakers and more" autocomplete="off" data-header-search-input><button class="header-search-submit" type="submit" aria-label="Open search" data-header-search-submit><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6"/><path d="m16 16 5 5"/></svg></button><button class="header-search-close" type="button" aria-label="Close search" data-header-search-close>×</button></form>
            <div class="header-cart">
                <button class="header-icon-link header-cart-toggle" type="button" data-cart-toggle aria-expanded="false" aria-controls="header-cart-dropdown" aria-label="Open shopping bag, {{ $headerCartCount }} items"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1.3 12H5.3L4 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><span class="header-count" data-header-cart-count>{{ $headerCartCount }}</span></button>
                <div class="header-cart-dropdown" id="header-cart-dropdown" data-cart-dropdown hidden>
                    <div data-mini-cart-content>@include('store.partials.mini-cart-content', ['cartItems' => $headerCartItems, 'cartCount' => $headerCartCount, 'cartSubtotal' => $headerCartSubtotal])</div>
                </div>
            </div>
            <a class="header-icon-link account-link" href="{{ auth()->check() ? route('dashboard.home') : route('login') }}" aria-label="{{ auth()->check() ? 'Account' : 'Sign in' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21a7 7 0 0 1 14 0"/></svg></a>
            @can('manage-store')<a class="cart-link admin-link" href="{{ route('admin.home') }}">Admin</a>@endcan
        </div>
    </div>
</header>
<main>@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif @yield('content')</main>
<div class="cart-toast-region" data-cart-toast-region aria-live="polite" aria-atomic="false"></div>
<footer class="footer storefront-footer">
    <div class="footer-background-layer" aria-hidden="true">
        <video class="footer-background-video" autoplay muted loop playsinline preload="none" tabindex="-1" disablepictureinpicture>
            <source src="{{ \App\Support\MediaUrl::siteAsset('footer_background_video', 'videos/footer-motion.mp4') }}" type="video/mp4">
        </video>
    </div>
    <div class="footer-social-bar">
        <strong>FOLLOW US</strong>
        <div class="footer-social-links">
            <a href="https://www.facebook.com/Crystalclearbeats/" target="_blank" rel="noopener noreferrer" aria-label="Crystal Car Audio on Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 21v-8.1h2.8l.4-3.2h-3.2v-2c0-.9.3-1.5 1.6-1.5h1.7V3.4c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.2v3.2H10V21h3.4Z"/></svg></a>
            <a href="https://ke.linkedin.com/company/crystal-car-audio" target="_blank" rel="noopener noreferrer" aria-label="Crystal Car Audio on LinkedIn">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.4 2H3.6C2.7 2 2 2.7 2 3.6v16.8c0 .9.7 1.6 1.6 1.6h16.8c.9 0 1.6-.7 1.6-1.6V3.6c0-.9-.7-1.6-1.6-1.6ZM8 18H5V9h3v9ZM6.5 7.8a1.7 1.7 0 1 1 0-3.4 1.7 1.7 0 0 1 0 3.4ZM19 18h-3v-4.4c0-1.1 0-2.5-1.5-2.5S13 12.3 13 13.5V18h-3V9h2.9v1.2h.1c.4-.7 1.3-1.5 2.8-1.5 3 0 3.5 2 3.5 4.5V18Z"/></svg>
            </a>
            <a href="https://www.instagram.com/crys_talcaraudio/" target="_blank" rel="noopener noreferrer" aria-label="Crystal Car Audio on Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2.2A2.8 2.8 0 0 0 4.2 7v10A2.8 2.8 0 0 0 7 19.8h10a2.8 2.8 0 0 0 2.8-2.8V7A2.8 2.8 0 0 0 17 4.2H7Zm5 2.1a5.7 5.7 0 1 1 0 11.4 5.7 5.7 0 0 1 0-11.4Zm0 2.2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.9-3a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6Z"/></svg></a>
            <a href="/find-us" aria-label="Find Crystal Car Audio on the map"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg></a>
        </div>
    </div>
    <div class="footer-updates">
        <p class="eyebrow">KEEP YOUR DRIVE IN TUNE</p>
        <h2>Good things are on the way.</h2>
        <p>Get product updates and offers from the Crystal Car Audio team.</p>
        <div class="footer-contact-options">
            <form class="footer-contact-bar" id="footer-email-updates">
                <span class="footer-contact-icon">✉</span><span class="footer-update-field"><small>EMAIL UPDATES</small><input name="email" type="email" autocomplete="email" placeholder="Enter your email address" aria-label="Email address for product updates" required></span><button type="submit">Subscribe <i>↗</i></button>
            </form>
            <form class="footer-contact-bar" id="footer-whatsapp-updates">
                <span class="footer-contact-icon">◉</span><span class="footer-update-field"><small>WHATSAPP UPDATES</small><input name="phone" type="tel" autocomplete="tel" placeholder="+254 7XX XXX XXX" aria-label="WhatsApp number for product updates" required></span><button type="submit">Subscribe <i>↗</i></button>
            </form>
        </div>
        <label class="footer-consent"><input id="footer-update-consent" type="checkbox" required><span>I have read and agree to the oraimo Store <a href="https://ke.oraimo.com/pages/terms-of-service">Terms of use</a> and <a href="https://ke.oraimo.com/pages/privacy-policy">Privacy Policy</a></span></label>
        <small class="footer-opt-in-note">Your email app or WhatsApp opens with a prefilled request. Your details are shared only if you send it.</small>
    </div>
    <div class="footer-columns">
        <div class="footer-column"><strong>Customer service</strong><span>Need help finding the right fit?</span><a href="tel:+254790721397">Call +254 790 721 397</a><a href="mailto:info@crystalcaraudio.com">info@crystalcaraudio.com</a><a href="https://wa.me/254790721397">WhatsApp our team</a></div>
        <div class="footer-column"><strong>Explore Crystal</strong><a href="{{ route('categories') }}">Shop all products</a><a href="{{ route('shop-by-category') }}">Shop by category</a><a href="{{ route('shop-by-brand') }}">Shop by brand</a><a href="{{ route('premium-car-audio') }}">Premium car audio in Nairobi</a><a href="{{ route('special-offers') }}">Special offers</a><a href="/find-us">About and find us</a><a href="{{ route('portfolio') }}">Installation portfolio</a></div>
        <div class="footer-column"><strong>Shop by category</strong><a href="/products/car-radio">Car radios</a><a href="{{ route('car-speakers') }}">Car speakers</a><a href="/products/car-amplifiers-booster">Amplifiers</a><a href="/products/conversion-fascia-kits">Fascia kits</a><a href="{{ route('shop-by-category') }}">View all categories</a></div>
        <div class="footer-column"><strong>Help & information</strong><a href="/shipping-policy">Delivery information</a><a href="/terms-and-conditions">Terms and conditions</a><a href="/privacy-policy">Privacy policy</a><a href="https://econfirm.co.ke/" target="_blank" rel="noopener noreferrer">eConfirm escrow service</a><a href="{{ route('login') }}">Orders and account</a></div>
    </div>
    <div class="footer-bottom"><div class="footer-bottom-inner"><small class="footer-meta"><span>© {{ date('Y') }} Crystal Car Audio. Nairobi, Kenya.</span><span class="footer-meta-divider" aria-hidden="true">|</span><span>Powered by <a href="https://designekta.com/" target="_blank" rel="noopener noreferrer">Designekta Studios</a></span></small></div></div>
</footer>
<nav class="mobile-bar" aria-label="Quick navigation">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home','home-page') ? 'is-active' : '' }}" @if(request()->routeIs('home','home-page')) aria-current="page" @endif aria-label="Home">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3.5 10.5 8.5-7 8.5 7v9a1 1 0 0 1-1 1h-5.2v-6.2h-4.6v6.2H4.5a1 1 0 0 1-1-1v-9Z"/></svg><small>Home</small>
    </a>
    <a href="{{ route('categories') }}" class="{{ request()->routeIs('categories','product-category','special-offers','product-single') ? 'is-active' : '' }}" @if(request()->routeIs('categories','product-category','special-offers','product-single')) aria-current="page" @endif aria-label="Shop products">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg><small>Shop</small>
    </a>
    <a href="{{ route('cart') }}" class="{{ request()->routeIs('cart','checkout') ? 'is-active' : '' }}" @if(request()->routeIs('cart','checkout')) aria-current="page" @endif aria-label="Bag, {{ collect(session('cart', []))->sum('quantity') }} items">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1.2 12H5.2L4 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><small>Bag {{ collect(session('cart', []))->sum('quantity') }}</small>
    </a>
</nav>
<button class="back-to-top" type="button" data-back-to-top aria-label="Back to top" title="Back to top" hidden>
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6.5 10.5 12 5l5.5 5.5"/></svg>
</button>
<section class="store-chat-panel" id="store-chat-panel" aria-label="Chat with Crystal Car Audio" hidden>
    <header class="store-chat-header">
        <span class="store-chat-avatar" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M16.03 3.2A12.74 12.74 0 0 0 5.08 22.45L3.3 28.9l6.6-1.73A12.8 12.8 0 1 0 16.03 3.2Zm0 23.37c-1.94 0-3.85-.52-5.52-1.51l-.4-.23-3.92 1.03 1.05-3.82-.26-.42a10.54 10.54 0 1 1 9.05 4.95Zm5.79-7.9c-.32-.16-1.88-.93-2.17-1.03-.29-.11-.5-.16-.71.16-.21.32-.82 1.03-1 1.24-.19.22-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.6-1.89-1.79-2.21-.18-.32-.02-.49.14-.65.14-.14.32-.37.48-.55.16-.19.21-.32.32-.54.1-.21.05-.4-.03-.56-.08-.16-.71-1.72-.98-2.36-.26-.62-.52-.53-.71-.54h-.61c-.21 0-.56.08-.85.4-.3.32-1.11 1.08-1.11 2.64s1.14 3.06 1.3 3.27c.16.21 2.24 3.42 5.43 4.8.76.33 1.35.53 1.81.68.76.24 1.46.21 2.01.13.61-.09 1.88-.77 2.15-1.51.26-.75.26-1.38.18-1.51-.08-.14-.29-.22-.61-.38Z"/></svg></span>
        <span class="store-chat-title"><strong>Crystal Car Audio Support</strong><small>Typically replies instantly</small></span>
        <button class="store-chat-close" type="button" data-store-chat-close aria-label="Close chat">×</button>
    </header>
    <div class="store-chat-content" aria-live="polite"><p>Hello! Welcome to Crystal Car Audio. How can we help you today?</p></div>
    <form class="store-chat-form" data-store-chat-form>
        <label class="sr-only" for="store-chat-message">Your message</label>
        <textarea id="store-chat-message" name="message" placeholder="Type your message here..." rows="1" maxlength="1000" required></textarea>
        <button type="submit" aria-label="Continue chat in WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-7.4 18-3.8-7.8L2 9.4 21 3Zm0 0L9.8 13.2"/></svg></button>
    </form>
    <footer class="store-chat-footer"><span>Direct chat via WhatsApp</span><span><i aria-hidden="true"></i>Online</span></footer>
</section>
<button class="store-chat-widget" type="button" data-store-chat-toggle aria-controls="store-chat-panel" aria-expanded="false" aria-label="Open WhatsApp chat with Crystal Car Audio" title="Chat with us">
    <svg class="store-chat-icon" viewBox="0 0 32 32" aria-hidden="true"><path d="M16.03 3.2A12.74 12.74 0 0 0 5.08 22.45L3.3 28.9l6.6-1.73A12.8 12.8 0 1 0 16.03 3.2Zm0 23.37c-1.94 0-3.85-.52-5.52-1.51l-.4-.23-3.92 1.03 1.05-3.82-.26-.42a10.54 10.54 0 1 1 9.05 4.95Zm5.79-7.9c-.32-.16-1.88-.93-2.17-1.03-.29-.11-.5-.16-.71.16-.21.32-.82 1.03-1 1.24-.19.22-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.6-1.89-1.79-2.21-.18-.32-.02-.49.14-.65.14-.14.32-.37.48-.55.16-.19.21-.32.32-.54.1-.21.05-.4-.03-.56-.08-.16-.71-1.72-.98-2.36-.26-.62-.52-.53-.71-.54h-.61c-.21 0-.56.08-.85.4-.3.32-1.11 1.08-1.11 2.64s1.14 3.06 1.3 3.27c.16.21 2.24 3.42 5.43 4.8.76.33 1.35.53 1.81.68.76.24 1.46.21 2.01.13.61-.09 1.88-.77 2.15-1.51.26-.75.26-1.38.18-1.51-.08-.14-.29-.22-.61-.38Z"/></svg>
    <svg class="store-chat-close-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
</button>
<script>
(() => {
    const mobileBar = document.querySelector('.mobile-bar');
    if (!mobileBar || !window.matchMedia('(max-width: 760px)').matches) return;
    let hideTimer;
    const hide = () => mobileBar.classList.remove('is-visible');
    const showWhileScrolling = () => {
        window.clearTimeout(hideTimer);
        if (window.scrollY < 12) {
            hide();
            return;
        }
        mobileBar.classList.add('is-visible');
        hideTimer = window.setTimeout(hide, 950);
    };
    window.addEventListener('scroll', showWhileScrolling, { passive: true });
    mobileBar.addEventListener('focusin', () => {
        window.clearTimeout(hideTimer);
        mobileBar.classList.add('is-visible');
    });
    mobileBar.addEventListener('focusout', () => {
        hideTimer = window.setTimeout(hide, 950);
    });
    if (window.scrollY >= 12) showWhileScrolling();
})();
</script>
</body></html>

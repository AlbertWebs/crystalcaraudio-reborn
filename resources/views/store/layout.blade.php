<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Crystal Car Audio | Sound that moves you')</title>
    <meta name="description" content="@yield('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon/favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('favicon/manifest.json') }}">
    <meta name="theme-color" content="#0c6661">
    <meta name="msapplication-config" content="{{ asset('favicon/browserconfig.xml') }}">
    <meta property="og:site_name" content="Crystal Car Audio">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Crystal Car Audio | Sound that moves you')">
    <meta property="og:description" content="@yield('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('uploads/logo/Crystal%20Logo-06.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'Crystal Car Audio | Sound that moves you')">
    <meta name="twitter:description" content="@yield('meta_description', 'Shop car audio, multimedia, speakers and accessories at Crystal Car Audio Kenya. Expert advice, secure checkout and delivery across Kenya.')">
    @php
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Crystal Car Audio',
            'url' => url('/'),
            'logo' => asset('uploads/logo/Crystal%20Logo-06.png'),
        ];
    @endphp
    <script type="application/ld+json">@json($organizationSchema)</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
@php
    $headerCategories = \Illuminate\Support\Facades\DB::table('catalog_categories')->orderByRaw('CASE WHEN sort_order = 0 THEN 1 ELSE 0 END')->orderBy('sort_order')->orderBy('name')->get(['name','slug']);
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
    <div class="header-topline"><a href="{{ route('home') }}">Crystal Car Audio <span>|</span> Nairobi</a><p>A better drive starts with better sound <i>·</i> Expert advice and installation across Kenya</p><a class="topline-contact" href="tel:+254790721397">Call +254 790 721 397</a></div>
    <div class="header-main">
        <a class="brand" href="{{ route('home') }}" aria-label="Crystal Car Audio home"><img class="brand-logo" src="{{ asset('uploads/logo/Crystal%20Logo-06.png') }}" alt="Crystal Car Audio"></a>
        <button class="menu-toggle" aria-label="Open navigation" data-menu-toggle aria-expanded="false">☰</button>
        <nav class="main-nav" data-main-nav aria-label="Main navigation">
            <a href="{{ route('categories') }}">Shop all</a>
            <details class="nav-category">
                <summary>{{ $headerAudioCategories->first()->name ?? 'Car audio' }} <span>⌄</span></summary>
                <div class="category-mega">
                    <div class="mega-category-list"><p class="mega-heading">SHOP BY CATEGORY</p>@foreach($headerAudioCategories as $category)<a href="{{ route('product-category', $category->slug) }}" @if($loop->first) class="is-active" @endif><i aria-hidden="true">{{ str_contains(strtolower($category->name), 'speaker') ? '◖' : (str_contains(strtolower($category->name), 'radio') ? '▤' : (str_contains(strtolower($category->name), 'amplifier') ? '◈' : '♫')) }}</i><span class="mega-category-name">{{ $category->name }}</span><b aria-hidden="true">›</b></a>@endforeach<a class="mega-all" href="{{ route('categories') }}"><i aria-hidden="true">↗</i><span class="mega-category-name">View all categories</span></a></div>
                    @if($featuredMenuProduct)<a class="mega-feature-product" href="{{ route('product-single', $featuredMenuProduct->slug) }}"><span class="mega-feature-image"><img src="{{ str_starts_with($featuredMenuProduct->image, 'http') ? $featuredMenuProduct->image : asset('uploads/'.$featuredMenuProduct->image) }}" alt=""></span><small>FEATURED PICK</small><strong>{{ \Illuminate\Support\Str::limit($featuredMenuProduct->name, 38) }}</strong><b>KES {{ number_format($featuredMenuProduct->price) }}</b></a>@endif
                    <div class="mega-product-area"><div class="mega-product-heading"><div><p class="eyebrow">CRYSTAL PICKS</p><h3>Popular upgrades</h3></div><div class="mega-heading-actions"><a href="{{ route('categories') }}">Shop all <span>↗</span></a><button class="mega-menu-close" type="button" data-menu-close aria-label="Close category menu">×</button></div></div><div class="mega-product-grid">@foreach($menuProducts->reject(fn($item) => $featuredMenuProduct && $item->slug === $featuredMenuProduct->slug) as $menuProduct)<a class="mega-product" href="{{ route('product-single', $menuProduct->slug) }}">@if($menuProduct->featured || $menuProduct->trending)<small class="mega-product-badge">New Arrival</small>@endif<span class="mega-product-image"><img src="{{ str_starts_with($menuProduct->image, 'http') ? $menuProduct->image : asset('uploads/'.$menuProduct->image) }}" alt=""></span><span class="mega-product-name">{{ \Illuminate\Support\Str::limit($menuProduct->name, 42) }}</span><strong>KES {{ number_format($menuProduct->price) }}</strong></a>@endforeach</div></div>
                    <aside class="mega-service-panel"><p class="eyebrow">CRYSTAL CAR AUDIO</p><h3>Expert help for your next upgrade</h3><div class="mega-service-perks"><span><i>◉</i> Product advice</span><span><i>⌁</i> Fitment support</span><span><i>↗</i> Kenya wide delivery</span><span><i>▣</i> Secure checkout</span></div><div class="mega-service-actions"><a href="https://wa.me/254790721397">WhatsApp our team <span>↗</span></a><a href="tel:+254790721397">Call Crystal <span>↗</span></a></div></aside>
                </div>
            </details>
            @foreach($headerAudioCategories->skip(1) as $category)
                <a href="{{ route('product-category', $category->slug) }}">{{ $category->name }}</a>
            @endforeach
            <a href="{{ route('special-offers') }}">Offers</a>
        </nav>
        <div class="header-actions">
            <form class="search" action="{{ route('search-results') }}" role="search" data-header-search aria-expanded="false"><label class="sr-only" for="header-search-input">Search Crystal Car Audio products</label><input id="header-search-input" name="q" value="{{ request('q') }}" placeholder="Search car radios, speakers and more" autocomplete="off" data-header-search-input><button class="header-search-submit" type="submit" aria-label="Open search" data-header-search-submit><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6"/><path d="m16 16 5 5"/></svg></button><button class="header-search-close" type="button" aria-label="Close search" data-header-search-close>×</button></form>
            <div class="header-cart">
                <button class="header-icon-link header-cart-toggle" type="button" data-cart-toggle aria-expanded="false" aria-controls="header-cart-dropdown" aria-label="Open shopping bag, {{ $headerCartCount }} items"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1.3 12H5.3L4 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><span class="header-count">{{ $headerCartCount }}</span></button>
                <div class="header-cart-dropdown" id="header-cart-dropdown" data-cart-dropdown hidden>
                    <div class="mini-cart-heading"><div><span class="eyebrow">YOUR SELECTION</span><h2>Your bag</h2></div><span class="mini-cart-count">{{ $headerCartCount }} {{ \Illuminate\Support\Str::plural('item', $headerCartCount) }}</span></div>
                    @if($headerCartItems->isNotEmpty())
                        <div class="mini-cart-items">
                            @foreach($headerCartItems as $item)
                                <article class="mini-cart-item">
                                    <div class="mini-cart-thumb">@if(!empty($item['image']))<img src="{{ str_starts_with($item['image'], 'http') ? $item['image'] : asset('uploads/'.$item['image']) }}" alt="">@else<span aria-hidden="true">◉</span>@endif</div>
                                    <div class="mini-cart-item-copy"><strong>{{ $item['name'] ?? 'Product' }}</strong><span>Qty {{ (int) ($item['quantity'] ?? 0) }}</span></div>
                                    <b>KES {{ number_format((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0)) }}</b>
                                </article>
                            @endforeach
                        </div>
                        <div class="mini-cart-subtotal"><span>Subtotal</span><strong>KES {{ number_format($headerCartSubtotal) }}</strong></div>
                        <div class="mini-cart-actions"><a href="{{ route('cart') }}">View bag</a><a href="{{ route('checkout') }}">Checkout <span aria-hidden="true">↗</span></a></div>
                    @else
                        <div class="mini-cart-empty"><span aria-hidden="true">▢</span><p>Your bag is empty.</p><a href="{{ route('categories') }}">Explore products <span aria-hidden="true">↗</span></a></div>
                    @endif
                </div>
            </div>
            <a class="header-icon-link account-link" href="{{ auth()->check() ? route('dashboard.home') : route('login') }}" aria-label="{{ auth()->check() ? 'Account' : 'Sign in' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21a7 7 0 0 1 14 0"/></svg></a>
            @can('manage-store')<a class="cart-link admin-link" href="{{ route('admin.home') }}">Admin</a>@endcan
        </div>
    </div>
</header>
<main>@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif @yield('content')</main>
<footer class="footer storefront-footer">
    <div class="footer-social-bar">
        <strong>FOLLOW US</strong>
        <div class="footer-social-links">
            <span title="Facebook" role="img" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 21v-8.1h2.8l.4-3.2h-3.2v-2c0-.9.3-1.5 1.6-1.5h1.7V3.4c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.2v3.2H10V21h3.4Z"/></svg></span>
            <a href="https://ke.linkedin.com/company/crystal-car-audio" target="_blank" rel="noopener noreferrer" aria-label="Crystal Car Audio on LinkedIn">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.4 2H3.6C2.7 2 2 2.7 2 3.6v16.8c0 .9.7 1.6 1.6 1.6h16.8c.9 0 1.6-.7 1.6-1.6V3.6c0-.9-.7-1.6-1.6-1.6ZM8 18H5V9h3v9ZM6.5 7.8a1.7 1.7 0 1 1 0-3.4 1.7 1.7 0 0 1 0 3.4ZM19 18h-3v-4.4c0-1.1 0-2.5-1.5-2.5S13 12.3 13 13.5V18h-3V9h2.9v1.2h.1c.4-.7 1.3-1.5 2.8-1.5 3 0 3.5 2 3.5 4.5V18Z"/></svg>
            </a>
            <span title="Instagram" role="img" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2.2A2.8 2.8 0 0 0 4.2 7v10A2.8 2.8 0 0 0 7 19.8h10a2.8 2.8 0 0 0 2.8-2.8V7A2.8 2.8 0 0 0 17 4.2H7Zm5 2.1a5.7 5.7 0 1 1 0 11.4 5.7 5.7 0 0 1 0-11.4Zm0 2.2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.9-3a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6Z"/></svg></span>
            <span title="TikTok" role="img" aria-label="TikTok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.6 5.2a5.8 5.8 0 0 1-3.5-3.5A6.8 6.8 0 0 1 15.8 0h-4.1v15.1a2.8 2.8 0 1 1-2.8-2.8c.4 0 .8.1 1.2.2V8.3a7 7 0 1 0 5.7 6.8V7.4a9.8 9.8 0 0 0 5.1 1.5V4.8c-.5 0-.9.1-1.3.4Z"/></svg></span>
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
        <div class="footer-column"><strong>Explore Crystal</strong><a href="{{ route('categories') }}">Shop all products</a><a href="{{ route('special-offers') }}">Special offers</a><a href="/find-us">About and find us</a><a href="/our-portfolio">Installation portfolio</a></div>
        <div class="footer-column"><strong>Shop by category</strong><a href="/products/car-radio">Car radios</a><a href="/products/mid-range-speakers">Car speakers</a><a href="/products/car-amplifiers-booster">Amplifiers</a><a href="/products/conversion-fascia-kits">Fascia kits</a></div>
        <div class="footer-column"><strong>Help & information</strong><a href="/shipping-policy">Delivery information</a><a href="/terms-and-conditions">Terms and conditions</a><a href="/privacy-policy">Privacy policy</a><a href="{{ route('login') }}">Orders and account</a></div>
    </div>
    <div class="footer-bottom"><a class="footer-bottom-logo" href="{{ route('home') }}" aria-label="Crystal Car Audio home"><img src="{{ asset('uploads/logo/Crystal%20Logo-06.png') }}" alt="Crystal Car Audio"></a><small class="footer-meta"><span>© {{ date('Y') }} Crystal Car Audio. Nairobi, Kenya.</span><span class="footer-meta-divider" aria-hidden="true">|</span><span>Powered By <a href="https://designekta.com/" target="_blank" rel="noopener noreferrer">Designekta Studios</a></span></small></div>
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
</body></html>

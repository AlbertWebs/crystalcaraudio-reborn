@extends('store.layout')
@section('title', 'Car Audio Dealer in Kenya | Crystal Car Audio')
@section('meta_description', 'Car radios, speakers and amplifiers from Crystal Car Audio in Nairobi, Kenya. Ask about fitment, installation and delivery across Kenya.')
@section('content')
<section class="hero hero-storefront">
    <div class="hero-copy">
        <p class="eyebrow eyebrow-accent">SHOP COMBO</p>
        <h1>Build a <span class="hero-sound-underline">sound</span><br>that moves you.</h1>
        <p class="hero-lead">Pair a powerful Android screen with rich door speakers for a more complete drive.</p>
        <div class="hero-actions">
            <a class="button button-dark" href="{{ route('categories') }}">Explore the combo <span>↗</span></a>
            <a class="text-link" href="{{ route('special-offers') }}">See current offers</a>
        </div>
        <div class="hero-proof"><span class="proof-icon">✦</span><span>Matched for a better drive<br><b>Expert advice from Crystal Car Audio</b></span></div>
    </div>
    <div class="hero-art hero-combo-art" aria-label="Shop combo: car door speakers and Android screen with underseat subwoofer">
        <div class="hero-orbit"></div><div class="sun"></div>
        <span class="hero-art-index">THE SOUND UPGRADE · 01</span>
        <div class="combo-product-pair">
            @foreach(['car-door-speakers-65-320w', 'android-screen-underseat-subwoofer'] as $comboSlug)
                @if($comboProduct = $comboProducts->get($comboSlug))
                    <a class="combo-product-card" href="{{ route('product-single', $comboProduct->slug) }}">
                        <span class="combo-product-image"><img src="{{ str_starts_with($comboProduct->image, 'http') ? $comboProduct->image : \App\Support\MediaUrl::url('uploads/'.$comboProduct->image) }}" alt="{{ $comboProduct->name }}" loading="eager" fetchpriority="high" decoding="async"></span>
                        <span class="combo-product-copy"><small>{{ $loop->first ? 'DOOR SPEAKERS' : 'ANDROID + BASS' }}</small><b>{{ $loop->first ? 'Fuller sound' : 'Smarter drives' }}</b></span>
                        <strong>KES {{ number_format($comboProduct->price) }} <span aria-hidden="true">↗</span></strong>
                    </a>
                @endif
            @endforeach
        </div>
        <div class="combo-art-note"><span>01</span> Two upgrades. One better drive.</div>
    </div>
</section>

<section class="service-strip service-cards" aria-label="Shopping benefits">
    <article><span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 6.5h11v11H3zM14 10h4.2l2.8 3v4.5h-7z"/><circle cx="7.5" cy="18" r="1.8"/><circle cx="18" cy="18" r="1.8"/><path d="M16 10v3h5"/></svg></span><div class="service-copy"><small>DELIVERY SUPPORT</small><b>Delivery across Kenya</b><p>We’ll help arrange delivery for your order.</p></div></article>
    <article><span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18M7 15h4"/><circle cx="17" cy="14" r="1.5"/></svg></span><div class="service-copy"><small>FLEXIBLE PAYMENT</small><b>Cash on Delivery</b><p>Pay when your order arrives.</p></div></article>
    <article><span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m14.5 6.2 3.3-3.3a5 5 0 0 1-6.6 6.6l-7.7 7.7a2.1 2.1 0 0 0 3 3l7.7-7.7a5 5 0 0 1 6.6-6.6l-3.3 3.3z"/></svg></span><div class="service-copy"><small>EXPERT HELP</small><b>Fitment &amp; installation</b><p>Get advice on fitment and setting up your gear.</p></div></article>
</section>

<section class="section categories-section">
    @php
        $categoryArtwork = [
            'car-radio' => ['image' => 'car-radio.webp', 'description' => 'Android & OEM car radios', 'featured' => true],
            'mid-range-speakers' => ['image' => 'car-speakers.webp', 'description' => 'Coaxial, component & subwoofers', 'featured' => true],
            'car-amplifiers-booster' => ['image' => 'car-amplifiers.webp', 'description' => 'Power up your sound'],
            'car-audio-installation' => ['image' => 'car-audio-installation.webp', 'description' => 'Professional & reliable fitment'],
            'car-security-system' => ['image' => 'car-audio-system.webp', 'description' => 'Complete sound system packages'],
            'car-accessories' => ['image' => 'car-security.webp', 'description' => 'Vehicle security & tracking'],
            'conversion-fascia-kits' => ['image' => 'fascia-kits.webp', 'description' => 'Dash kits for a perfect fit'],
            'equalizers' => ['image' => 'equalizers.webp', 'description' => 'Fine-tune your sound'],
            'led-lights' => ['image' => 'in-car-accessories.webp', 'description' => 'Enhance comfort, style & convenience'],
        ];
        $featuredCategories = $categories->filter(fn ($category) => ($categoryArtwork[$category->slug]['featured'] ?? false));
        $categoryDisplayOrder = ['car-amplifiers-booster' => 1, 'car-audio-installation' => 2, 'car-security-system' => 3, 'car-accessories' => 4, 'conversion-fascia-kits' => 5, 'equalizers' => 6, 'led-lights' => 7];
        $otherCategories = $categories->reject(fn ($category) => ($categoryArtwork[$category->slug]['featured'] ?? false))
            ->sortBy(fn ($category) => $categoryDisplayOrder[$category->slug] ?? 99)->values();
    @endphp
    <div class="section-head category-section-head">
        <div class="category-section-title"><p class="eyebrow eyebrow-accent">FIND YOUR FREQUENCY</p><h2>Shop by <span>Category</span></h2><p class="section-subtitle">Premium car audio, security and in-car upgrades. Choose a category to explore our range.</p></div>
        <div class="category-trust-row" aria-label="Shopping benefits">
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.8 15 4l3.2-.1.9 3 2.1 2.4-1.3 3 0 3.2-2.8 1.5-1.7 2.7-3.2-.6-3.1.6-1.7-2.7-2.8-1.5 0-3.2-1.3-3 2.1-2.4.9-3L9 4z"/><path d="m8.7 12.1 2.2 2.2 4.5-4.7"/></svg>Genuine<br>products</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.7"/><circle cx="18" cy="18" r="1.7"/><path d="M15 10v4h6"/></svg>Fast delivery<br>across Kenya</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.8 20 6v5.6c0 4.5-3.1 7.9-8 9.6-4.9-1.7-8-5.1-8-9.6V6z"/><path d="m8.5 12.2 2.2 2.2 4.8-5"/></svg>Expert<br>installation</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 12H2.8v5H6v-5zM20 12h1.2v5H18v-5zM18 18c-.8 2-2.8 3-5.5 3"/></svg>After-sales<br>support</span>
        </div>
    </div>
    <div class="category-feature-grid">
        @foreach($featuredCategories as $category)
            @php($artwork = $categoryArtwork[$category->slug] ?? null)
            <a href="{{ route('product-category', $category->slug) }}" class="category-feature-card">
                @if($artwork)<img src="{{ asset('images/categories/'.$artwork['image']) }}" alt="{{ $category->name }}" loading="lazy" decoding="async">@endif
                <span class="category-feature-copy"><span class="category-feature-kicker">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} <i>·</i> PREMIUM AUDIO</span><h3>{{ $category->name }}</h3><span class="category-feature-description">{{ $artwork['description'] ?? 'Explore our collection' }}</span><span class="category-feature-cta"><span>{{ $category->product_count }} products</span><b aria-hidden="true">→</b></span></span>
                <span class="category-feature-arrow" aria-hidden="true">↗</span>
            </a>
        @endforeach
    </div>
    <div class="category-tile-grid">
        @foreach($otherCategories as $category)
            @php($artwork = $categoryArtwork[$category->slug] ?? null)
            <a href="{{ route('product-category', $category->slug) }}" class="category-tile-card">
                @if($artwork)<img src="{{ asset('images/categories/'.$artwork['image']) }}" alt="" loading="lazy" decoding="async">@endif
                <span class="category-tile-copy"><span class="category-tile-kicker">{{ str_pad((string) ($loop->iteration + $featuredCategories->count()), 2, '0', STR_PAD_LEFT) }} <i>·</i> CATEGORY</span><h3>{{ $category->name }}</h3><span class="category-tile-description">{{ $artwork['description'] ?? 'Explore our collection' }}</span><span class="category-tile-cta">{{ $category->product_count }} products <b aria-hidden="true">→</b></span></span>
            </a>
        @endforeach
    </div>
</section>

@if($deals->isNotEmpty())
<section class="product-collection collection-deals">
    <div class="collection-heading"><div><p class="eyebrow eyebrow-accent">A LITTLE MORE FOR LESS</p><h2>Current offers</h2><p>Good upgrades, at a better price.</p></div><div class="collection-actions"><a class="text-link" href="{{ route('special-offers') }}">Shop all offers ↗</a><button type="button" class="rail-button" data-scroll-rail="offers" data-scroll-by="-1" aria-label="Scroll offers left">←</button><button type="button" class="rail-button" data-scroll-rail="offers" data-scroll-by="1" aria-label="Scroll offers right">→</button></div></div>
    <div class="product-rail" id="offers">@foreach($deals as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div>
</section>
@endif

<section class="feature-banner">
    <img class="feature-banner-dashboard" src="{{ asset('images/feature-dashboard-night.webp') }}" alt="" loading="lazy" decoding="async" aria-hidden="true">
    <div class="feature-banner-copy"><p class="eyebrow eyebrow-accent">BUILT FOR THE MOMENT</p><h2>Feel the music.<br>Keep the road.</h2><p>From head units to powerful bass, find the right pieces for your setup.</p><a href="/products/car-audio" class="button button-light">Build your audio setup <span>↗</span></a></div>
</section>

<section class="product-collection">
    <div class="collection-heading"><div><p class="eyebrow eyebrow-accent">CUSTOMER FAVOURITES</p><h2>Popular right now</h2><p>Well-loved picks from the Crystal collection.</p></div><div class="collection-actions"><a class="text-link" href="{{ route('categories') }}">View the collection ↗</a><button type="button" class="rail-button" data-scroll-rail="popular" data-scroll-by="-1" aria-label="Scroll popular products left">←</button><button type="button" class="rail-button" data-scroll-rail="popular" data-scroll-by="1" aria-label="Scroll popular products right">→</button></div></div>
    <div class="product-rail" id="popular">@foreach($bestSellers as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div>
</section>

@if($brands->isNotEmpty())
<section class="brand-collection"><div class="brand-collection-title"><p class="eyebrow eyebrow-accent">BRANDS YOU KNOW</p><h2>Find your favourite</h2></div><div class="brand-pills">@foreach($brands as $brand)<a href="{{ route('products.brand', $brand) }}">{{ $brand }} <span>↗</span></a>@endforeach</div></section>
@endif

<section class="product-collection collection-new">
    <div class="collection-heading"><div><p class="eyebrow eyebrow-accent">FRESH IN THE SHOP</p><h2>New arrivals</h2><p>Recently added upgrades and accessories.</p></div><div class="collection-actions"><a class="text-link" href="{{ route('categories') }}">See all products ↗</a><button type="button" class="rail-button" data-scroll-rail="new-arrivals" data-scroll-by="-1" aria-label="Scroll new arrivals left">←</button><button type="button" class="rail-button" data-scroll-rail="new-arrivals" data-scroll-by="1" aria-label="Scroll new arrivals right">→</button></div></div>
    <div class="product-rail" id="new-arrivals">@foreach($newArrivals as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div>
</section>

<section class="why-crystal"><div class="why-heading"><p class="eyebrow eyebrow-accent">GOOD SOUND, GOOD SUPPORT</p><h2>We’re here for the whole setup.</h2><p>Pick the right gear, get it installed, and enjoy the drive.</p></div><div class="why-grid"><article><span>01</span><b>Advice that fits your car</b><p>Tell us your make and model and we’ll help you choose compatible equipment.</p><a href="/find-us">Ask our team ↗</a></article><article><span>02</span><b>Installation from people who know audio</b><p>Get support for installation and setup from the Crystal Car Audio team.</p><a href="/find-us">Plan an installation ↗</a></article><article><span>03</span><b>Checkout made clear</b><p>Choose Cash on Delivery or pay through eConfirm escrow at checkout.</p><a href="{{ route('categories') }}">Start shopping ↗</a></article></div></section>

<section class="expert-cta expert-cta-store"><div><p class="eyebrow eyebrow-accent">MADE TO FIT YOUR DRIVE</p><h2>Need a hand<br>choosing?</h2><p>Tell us about your car and we’ll help you find the right fit.</p><a class="button button-dark" href="/find-us">Talk to our team <span>↗</span></a></div><div class="expert-visual"><img loading="lazy" decoding="async" src="{{ asset('images/expert-car-audio-visual.webp') }}" alt="Premium car speaker and touchscreen audio system"></div></section>
@endsection

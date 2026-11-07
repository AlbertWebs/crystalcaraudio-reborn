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
    <div class="section-head"><div><p class="eyebrow eyebrow-accent">FIND YOUR FREQUENCY</p><h2>Shop by category</h2><p class="section-subtitle">Start with the upgrade you have in mind.</p></div><a class="text-link" href="{{ route('categories') }}">View all categories ↗</a></div>
    <div class="category-grid category-grid-live">
        @foreach($categories as $category)
            <a href="{{ route('product-category', $category->slug) }}" class="category-card category-card-live">
                @if($category->image)<img loading="lazy" decoding="async" src="{{ str_starts_with($category->image, 'http') ? $category->image : \App\Support\MediaUrl::url('uploads/'.$category->image) }}" alt="">@endif
                <span class="cat-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="category-card-info"><span><h3>{{ $category->name }}</h3><span class="category-product-count">{{ $category->product_count }} products</span></span><span class="category-arrow" aria-hidden="true">↗</span></span>
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

@extends('store.layout')

@section('title', 'Crystal Car Audio - Clear Beats with Premium Car Sound Systems in Nairobi')
@section('meta_description', 'Crystal Car Audio offers premium vehicle sound systems and accessories in Nairobi. Discover top-quality car radios, speakers, boosters, equalizers, and car alarm systems. Get crystal-clear sound and reliable security solutions for your vehicle today.')
@section('meta_image', $heroImageUrl)

@push('structured-data')
    @php
        $pageUrl = url('/premium-car-audio-systems-nairobi-kenya');
        $breadcrumbSchema = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => route('categories')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Premium car audio systems in Nairobi, Kenya', 'item' => $pageUrl],
            ],
        ];
        $webPageSchema = [
            '@type' => 'WebPage',
            '@id' => $pageUrl.'#webpage',
            'url' => $pageUrl,
            'name' => 'Premium Car Audio Systems in Nairobi, Kenya | Crystal Car Audio',
            'description' => 'Explore car radios, speakers, amplifiers and equalizers from Crystal Car Audio in Nairobi, Kenya.',
            'inLanguage' => 'en-KE',
            'isPartOf' => ['@id' => rtrim(url('/'), '/').'#website'],
            'about' => ['@id' => rtrim(url('/'), '/').'#localbusiness'],
        ];
    @endphp
    <script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => [$webPageSchema, $breadcrumbSchema]])</script>
@endpush

@section('content')
<section class="premium-audio-hero">
    <div class="premium-audio-shell premium-audio-hero-grid">
        <div class="premium-audio-hero-copy">
            <nav class="premium-audio-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span>
                <a href="{{ route('categories') }}">Products</a><span aria-hidden="true">/</span>
                <span aria-current="page">Car audio in Nairobi</span>
            </nav>
            <p class="eyebrow eyebrow-accent">CRYSTAL CAR AUDIO · NAIROBI, KENYA</p>
            <h1>Premium car audio systems in <span>Nairobi, Kenya</span></h1>
            <p class="premium-audio-lead">Find the right car radio, speakers and sound upgrades for your drive. Shop products from Crystal Car Audio and talk to our team about fitment, installation and delivery.</p>
            <div class="premium-audio-actions">
                <a class="button button-dark" href="#audio-systems">Explore audio systems <span aria-hidden="true">↘</span></a>
                <a class="text-link" href="{{ route('page.find-us') }}">Talk to Crystal ↗</a>
            </div>
            <ul class="premium-audio-trust" aria-label="Crystal Car Audio services">
                <li><span aria-hidden="true">✓</span> Product advice</li>
                <li><span aria-hidden="true">✓</span> Fitment support</li>
                <li><span aria-hidden="true">✓</span> Delivery across Kenya</li>
            </ul>
        </div>
        <div class="premium-audio-hero-art" aria-label="Car audio products available at Crystal Car Audio">
            <div class="premium-audio-art-orbit" aria-hidden="true"></div>
            <figure class="premium-audio-art-main">
                <img src="{{ \App\Support\MediaUrl::url('uploads/product/7-android-140-screen-with-carplay-thumbnail.png') }}" alt="Android car radio with a touchscreen display" loading="eager" fetchpriority="high" decoding="async">
                <figcaption>Upgrade your in-car sound</figcaption>
            </figure>
            <div class="premium-audio-art-note"><span>CRYSTAL</span><b>Sound for<br>the road ahead.</b></div>
        </div>
    </div>
</section>

<section class="premium-audio-intro premium-audio-shell" aria-labelledby="premium-audio-intro-title">
    <div><p class="eyebrow eyebrow-accent">A BETTER SETUP STARTS HERE</p><h2 id="premium-audio-intro-title">Build a car sound system that fits your drive.</h2></div>
    <div><p>Whether you want clearer calls, easy phone connectivity or deeper bass, choosing compatible equipment makes the difference. Browse our car audio range, compare product details and ask us to help check what will work with your vehicle.</p><a href="{{ route('page.find-us') }}">Get fitment advice <span aria-hidden="true">↗</span></a></div>
</section>

<div id="audio-systems" class="premium-audio-sections">
    @foreach($sections as $section)
        <section class="premium-audio-section {{ $loop->even ? 'is-reversed' : '' }}" aria-labelledby="audio-section-{{ $loop->iteration }}">
            <div class="premium-audio-shell premium-audio-section-grid">
                <div class="premium-audio-section-copy">
                    <p class="eyebrow eyebrow-accent">{{ $section['eyebrow'] }}</p>
                    <h2 id="audio-section-{{ $loop->iteration }}">{{ $section['title'] }}</h2>
                    <p>{{ $section['description'] }}</p>
                    <a class="text-link" href="{{ $section['link'] }}">{{ $section['link_label'] }} <span aria-hidden="true">↗</span></a>
                </div>
                <div class="premium-audio-product-grid" aria-label="{{ $section['link_label'] }}">
                    @forelse($section['products'] as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @empty
                        <div class="premium-audio-empty">Browse the full collection to see available products.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endforeach
</div>

<section class="premium-audio-guide premium-audio-shell" aria-labelledby="premium-audio-guide-title">
    <div class="premium-audio-guide-heading"><p class="eyebrow eyebrow-accent">GOOD SOUND, WELL MATCHED</p><h2 id="premium-audio-guide-title">What to consider before you upgrade</h2></div>
    <div class="premium-audio-guide-grid">
        <article><span>01</span><h3>Your vehicle</h3><p>Radio sizes, wiring and speaker fitment differ between vehicles. Share your make, model and year when asking for advice.</p></article>
        <article><span>02</span><h3>Your listening style</h3><p>Choose components around what matters to you, from clear vocals and hands-free calls to stronger low-frequency sound.</p></article>
        <article><span>03</span><h3>Your complete setup</h3><p>Check how the head unit, speakers, amplifier and accessories work together before purchasing parts for a full system.</p></article>
    </div>
</section>

<section class="premium-audio-local">
    <div class="premium-audio-shell premium-audio-local-inner">
        <div><p class="eyebrow">VISIT OR CONTACT OUR TEAM</p><h2>Car audio advice in Nairobi.<br>Support across Kenya.</h2><p>Visit Crystal Car Audio at Roasters on Garden Estate Road, opposite MCK Ruaraka Church and next to Big Fish. We can help you explore products and discuss fitment for your car.</p></div>
        <div class="premium-audio-local-card"><span>CRYSTAL CAR AUDIO</span><b>Roasters, Garden Estate Road</b><p>Opposite MCK Ruaraka Church, next to Big Fish<br>Nairobi, Kenya</p><a href="tel:+254790721397">+254 790 721 397</a><a href="mailto:info@crystalcaraudio.com">info@crystalcaraudio.com</a><a class="button button-light" href="{{ route('page.find-us') }}">Find us <span aria-hidden="true">↗</span></a></div>
    </div>
</section>
@endsection

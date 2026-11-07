@extends('store.layout')
@section('title', $product->name.' | Crystal Car Audio')
@section('content')
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('categories') }}">Shop</a><span>›</span><span>{{ $product->category ?? 'Product' }}</span></nav>
<section class="product-detail-page">
    @php
        $gallery = collect($product->gallery ?? [])->filter()->unique()->values();
        if ($gallery->isEmpty() && $product->image) $gallery = collect([$product->image]);
        $discountPercent = $product->original_price && $product->original_price > $product->price ? (int) round((($product->original_price - $product->price) / $product->original_price) * 100) : null;
        $inStock = !str_contains(strtolower((string) $product->stock), 'out');
    @endphp
    <div class="product-gallery">
        <div class="product-gallery-main">
            @if($discountPercent)<span class="product-badge badge-sale">Save {{ $discountPercent }}%</span>@endif
            @if($gallery->isNotEmpty())
                <img id="product-gallery-image" src="{{ str_starts_with($gallery->first(), 'http') ? $gallery->first() : \App\Support\MediaUrl::url('uploads/'.$gallery->first()) }}" alt="{{ $product->name }}" loading="eager" fetchpriority="high" decoding="async">
            @else
                <span class="product-placeholder">◉</span>
            @endif
        </div>
        @if($gallery->count() > 1)
            <div class="product-thumbnails" aria-label="Product images">
                @foreach($gallery as $image)
                    <button type="button" data-gallery-image="{{ str_starts_with($image, 'http') ? $image : \App\Support\MediaUrl::url('uploads/'.$image) }}" class="{{ $loop->first ? 'is-active' : '' }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-label="Show image {{ $loop->iteration }}"><img loading="lazy" decoding="async" src="{{ str_starts_with($image, 'http') ? $image : \App\Support\MediaUrl::url('uploads/'.$image) }}" alt=""></button>
                @endforeach
            </div>
        @endif
        <div class="gallery-note"><span>✓</span> Product images from Crystal Car Audio</div>
    </div>

    <div class="product-purchase-panel">
        <p class="eyebrow">{{ $product->brand ?: $product->category ?: 'CRYSTAL COLLECTION' }}</p>
        <div class="product-title-row"><h1>{{ $product->name }}</h1><div class="product-title-save">
            @auth<form method="post" action="{{ route('wishlist.add', $product) }}">@csrf<button type="submit" aria-label="Save {{ $product->name }} to wishlist">♡</button></form>
            @else<a href="{{ route('login') }}" aria-label="Sign in to save this product">♡</a>@endauth
        </div></div>
        @if($product->rating)
            <p class="detail-rating"><span aria-label="{{ number_format($product->rating, 1) }} out of 5 stars">{{ str_repeat('★', (int) round($product->rating)) }}{{ str_repeat('☆', 5 - (int) round($product->rating)) }}</span> @if($product->review_count) <a href="#product-reviews">({{ $product->review_count }})</a>@endif <a href="#product-reviews">Write a review ›</a></p>
        @endif
        <div class="detail-price-block"><strong>KES {{ number_format($product->price) }}</strong>@if($discountPercent)<del>KES {{ number_format($product->original_price) }}</del><span class="save-chip">Save {{ $discountPercent }}%</span>@endif</div>
        @if($shortSpecifications->isNotEmpty())
            <div class="detail-feature-list">
                @foreach($shortSpecifications->take(4) as $spec)
                    <article><span aria-hidden="true">{{ $loop->iteration === 1 ? '◉' : ($loop->iteration === 2 ? '⌘' : ($loop->iteration === 3 ? '▦' : '↗')) }}</span><p>{{ $spec['title'] }}: {{ $spec['value'] }}</p></article>
                @endforeach
            </div>
        @else
            <p class="product-description">{{ \Illuminate\Support\Str::limit($descriptionPlain ?: 'A considered upgrade for your drive. Contact our team for compatibility and installation advice.', 220) }}</p>
        @endif

        <div class="detail-stock-line"><span class="stock-dot {{ $inStock ? '' : 'is-out' }}"></span><b>{{ $inStock ? 'In stock' : 'Currently unavailable' }}</b>@if($product->sku)<small>SKU {{ $product->sku }}</small>@endif</div>
        <form id="detail-purchase-form" class="detail-purchase-form" method="post" action="{{ route('cart.add', $product) }}">
            @csrf
            <label for="quantity">Qty</label><input id="quantity" name="quantity" type="number" min="1" max="10" value="1">
        </form>
        <div class="delivery-detail-cards">
            <article><span>⌑</span><div><b>Safe payments</b><small>Cash on Delivery or eConfirm</small></div></article>
            <article><span>↗</span><div><b>Secure delivery</b><small>Delivery across Kenya</small></div></article>
            <article><span>◉</span><div><b>Fitment support</b><small>Advice from our team</small></div></article>
        </div>
        <div class="detail-secondary-actions">
            <a class="text-link" href="/find-us">Ask about this product ↗</a>
        </div>
    </div>
</section>

@if($relatedProducts->isNotEmpty())
<section class="product-collection related-collection"><div class="collection-heading"><div><p class="eyebrow">MORE FROM {{ strtoupper($product->category ?? 'CRYSTAL') }}</p><h2>Related products</h2><p>More gear that fits the same kind of setup.</p></div><div class="collection-actions"><a class="text-link" href="{{ route('product-category', \Illuminate\Support\Str::slug($product->category ?? '')) }}">View category ↗</a><button type="button" class="rail-button" data-scroll-rail="related" data-scroll-by="-1" aria-label="Scroll related products left">←</button><button type="button" class="rail-button" data-scroll-rail="related" data-scroll-by="1" aria-label="Scroll related products right">→</button></div></div><div class="product-rail" id="related">@foreach($relatedProducts as $related)@include('store.partials.product-card', ['product' => $related])@endforeach</div></section>
@endif
<section class="product-information" id="product-information">
    <nav class="product-information-nav" aria-label="Product information"><a href="#product-description">Description</a><a href="#product-specifications">Specifications</a><a href="#product-reviews">Reviews <span>({{ $reviews->count() }})</span></a></nav>
    <div class="product-information-grid">
        <article class="product-description-panel" id="product-description">
            <p class="eyebrow">PRODUCT DETAILS</p><h2>Made for the way you drive.</h2>
            @forelse($detailParagraphs as $paragraph)<p>{{ $paragraph }}</p>@empty<p>Contact our team for more information about this product and compatibility with your vehicle.</p>@endforelse
        </article>
        <aside class="product-specification-panel" id="product-specifications">
            <p class="eyebrow">AT A GLANCE</p><h2>Specifications</h2>
            @if($shortSpecifications->isNotEmpty())
                <dl>@foreach($shortSpecifications as $spec)<div><dt>{{ $spec['title'] }}</dt><dd>{{ $spec['value'] }}</dd></div>@endforeach
                @if($product->brand)<div><dt>Brand</dt><dd>{{ $product->brand }}</dd></div>@endif
                @if($product->sku)<div><dt>Product code</dt><dd>{{ $product->sku }}</dd></div>@endif</dl>
            @else
                <dl>@if($product->brand)<div><dt>Brand</dt><dd>{{ $product->brand }}</dd></div>@endif @if($product->category)<div><dt>Category</dt><dd>{{ $product->category }}</dd></div>@endif @if($product->sku)<div><dt>Product code</dt><dd>{{ $product->sku }}</dd></div>@endif</dl>
            @endif
        </aside>
    </div>
</section>
<section class="product-reviews" id="product-reviews">
    <div class="reviews-heading"><div><p class="eyebrow">DRIVER FEEDBACK</p><h2>Customer reviews</h2><p>Reviews shared by Crystal customers.</p></div>@if($reviews->isNotEmpty())<div class="reviews-score"><strong>{{ number_format($reviews->avg('rating'), 1) }}</strong><span>★★★★★</span><small>{{ $reviews->count() }} {{ \Illuminate\Support\Str::plural('review', $reviews->count()) }}</small></div>@endif</div>
    @forelse($reviews as $review)<article class="customer-review"><div class="review-stars" aria-label="{{ number_format($review['rating'], 1) }} out of 5 stars">{{ str_repeat('★', (int) round($review['rating'])) }}{{ str_repeat('☆', 5 - (int) round($review['rating'])) }}</div><h3>{{ $review['title'] ?: 'Customer review' }}</h3>@if($review['content'])<p>{{ $review['content'] }}</p>@endif<div><b>{{ $review['name'] ?: 'Crystal customer' }}</b>@if($review['created_at'])<small>{{ \Illuminate\Support\Carbon::parse($review['created_at'])->format('M Y') }}</small>@endif</div></article>@empty
        <div class="reviews-empty"><span>☆</span><div><b>No reviews yet</b><p>Be the first to share your experience with this product.</p></div></div>
    @endforelse
</section>
<div class="product-action-dock"><button class="button button-light" type="submit" form="detail-purchase-form" name="buy_now" value="0" @if(!$inStock) disabled @endif>Add to bag</button><button class="button button-dark" type="submit" form="detail-purchase-form" name="buy_now" value="1" @if(!$inStock) disabled @endif>Buy now</button></div>
@endsection

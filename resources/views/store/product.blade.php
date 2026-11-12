@extends('store.layout')
@php
    $productImageUrls = collect($product->gallery ?? [])->filter()->push($product->image)->filter()->unique()->take(10)
        ->map(fn ($image) => str_starts_with($image, 'http')
            ? $image
            : \App\Support\MediaUrl::url('uploads/'.ltrim($image, '/')))
        ->values();
    $productDescriptionForSeo = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $descriptionPlain), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $seoTitle = filled($product->meta_title)
        ? trim($product->meta_title)
        : $product->name.' | Crystal Car Audio';
    $seoDescription = filled($product->meta_description)
        ? trim(strip_tags($product->meta_description))
        : 'Shop '.$product->name.($product->category ? ' in '.$product->category : '').' at Crystal Car Audio in Nairobi, Kenya. KES '.number_format($product->price).'. Get expert fitment advice and delivery across Kenya.';
    $seoDescription = \Illuminate\Support\Str::limit($seoDescription, 160);
    $productAvailability = str_contains(strtolower((string) $product->stock), 'out')
        ? 'OutOfStock'
        : (str_contains(strtolower((string) $product->stock), 'pre') ? 'PreOrder' : 'InStock');
    $productUrl = route('product-single', $product->slug);
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        '@id' => $productUrl.'#product',
        'name' => $product->name,
        'description' => $productDescriptionForSeo ?: $seoDescription,
        'image' => $productImageUrls->all(),
        'category' => $product->category,
        'offers' => [
            '@type' => 'Offer',
            'url' => $productUrl,
            'priceCurrency' => 'KES',
            'price' => number_format((float) $product->price, 2, '.', ''),
            'availability' => 'https://schema.org/'.$productAvailability,
            'seller' => ['@type' => 'Organization', 'name' => 'Crystal Car Audio'],
        ],
    ];
    if (filled($product->sku)) {
        $productSchema['sku'] = $product->sku;
    }
    $manufacturerBrand = filled($product->brand) && ! in_array(strtolower(trim($product->brand)), ['other', 'others', 'unknown', 'n/a', 'na', 'none'], true);
    if (filled($product->gtin)) {
        $productSchema['gtin'] = preg_replace('/\D+/', '', (string) $product->gtin);
    }
    $manufacturerPartNumber = filled($product->mpn) ? trim($product->mpn) : ($manufacturerBrand ? trim((string) $product->sku) : '');
    if ($manufacturerPartNumber !== '') {
        $productSchema['mpn'] = $manufacturerPartNumber;
    }
    if ($manufacturerBrand) {
        $productSchema['brand'] = ['@type' => 'Brand', 'name' => $product->brand];
    }
    if ($reviews->isNotEmpty()) {
        $productSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => round($reviews->avg('rating'), 1),
            'reviewCount' => $reviews->count(),
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
@endphp
@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', implode(', ', array_filter([$product->name, $product->brand, $product->category, 'car audio Nairobi', 'Crystal Car Audio Kenya'])))
@section('meta_og_title', $seoTitle)
@section('meta_og_description', $seoDescription)
@section('meta_og_type', 'product')
@section('meta_image', $productImageUrls->first() ?? \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png'))
@section('meta_product_price', number_format((float) $product->price, 2, '.', ''))
@section('meta_product_currency', 'KES')
@section('meta_product_availability', strtolower($productAvailability))
@if($manufacturerBrand)
    @section('meta_product_brand', $product->brand)
@endif
@section('meta_product_sku', $product->sku)
@push('structured-data')
    @if($productImageUrls->isNotEmpty() && (float) $product->price > 0)
        <script type="application/ld+json">@json($productSchema)</script>
    @endif
@endpush
@section('content')
@php
    $productBreadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Products', 'url' => route('categories')],
    ];
    $productCategoryUrl = $categoryRecord
        ? ($categoryRecord->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $categoryRecord->slug))
        : ($product->category
            ? (\Illuminate\Support\Str::slug($product->category) === 'car-speakers' ? route('car-speakers') : route('product-category', \Illuminate\Support\Str::slug($product->category)))
            : route('categories'));
    if (filled($product->category)) {
        $productBreadcrumbs[] = [
            'name' => $product->category,
            'url' => $productCategoryUrl,
        ];
    }
    if (filled($product->brand) && strtolower($product->brand) !== 'others') {
        $productBreadcrumbs[] = ['name' => $product->brand, 'url' => route('products.brand', $product->brand)];
    }
    $productBreadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn ($item, $index) => [
            '@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'],
            'item' => $item['url'] ?? route('product-single', $product->slug),
        ], [...$productBreadcrumbs, ['name' => $product->name]], array_keys([...$productBreadcrumbs, ['name' => $product->name]])),
    ];
@endphp
<nav class="breadcrumbs product-breadcrumbs" aria-label="Breadcrumb"><ol>@foreach($productBreadcrumbs as $item)<li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>@endforeach<li aria-current="page">{{ $product->name }}</li></ol></nav>
<script type="application/ld+json">@json($productBreadcrumbSchema)</script>
<section class="product-detail-page">
    @php
        $gallery = collect($product->gallery ?? [])->filter()->push($product->image)->filter()->unique()->values();
        $discountPercent = $product->original_price && $product->original_price > $product->price ? (int) round((($product->original_price - $product->price) / $product->original_price) * 100) : null;
        $inStock = !str_contains(strtolower((string) $product->stock), 'out');
    @endphp
    <div class="product-gallery">
        <div class="product-gallery-main">
            @if($discountPercent)<span class="product-badge badge-sale">Save {{ $discountPercent }}%</span>@endif
            @if($gallery->isNotEmpty())
                <img id="product-gallery-image" src="{{ str_starts_with($gallery->first(), 'http') ? $gallery->first() : \App\Support\MediaUrl::url('uploads/'.$gallery->first()) }}" alt="{{ $product->name }} product image" loading="eager" fetchpriority="high" decoding="async">
            @else
                <span class="product-placeholder">◉</span>
            @endif
        </div>
        @if($gallery->count() > 1)
            <div class="product-thumbnails" aria-label="Product images">
                @foreach($gallery as $image)
                    <button type="button" data-gallery-image="{{ str_starts_with($image, 'http') ? $image : \App\Support\MediaUrl::url('uploads/'.$image) }}" class="{{ $loop->first ? 'is-active' : '' }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-label="Show {{ $product->name }} image {{ $loop->iteration }}"><img loading="lazy" decoding="async" src="{{ str_starts_with($image, 'http') ? $image : \App\Support\MediaUrl::url('uploads/'.$image) }}" alt="{{ $product->name }} product view {{ $loop->iteration }}"></button>
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
            <button class="text-link product-enquiry-trigger" type="button" data-open-product-enquiry>Ask about this product ↗</button>
        </div>
    </div>
</section>

<dialog class="product-enquiry-dialog" id="product-enquiry-dialog" aria-labelledby="product-enquiry-title">
    <form class="product-enquiry-form" data-product-enquiry-form data-product-name="{{ $product->name }}" data-product-url="{{ route('product-single', $product->slug) }}">
        <div class="product-enquiry-heading">
            <div><p class="eyebrow">PRODUCT ENQUIRY</p><h2 id="product-enquiry-title">Ask about {{ $product->name }}</h2></div>
            <button class="product-enquiry-close" type="button" data-close-product-enquiry aria-label="Close enquiry">×</button>
        </div>
        <p class="product-enquiry-intro">Share your question and contact details. We’ll open WhatsApp with your enquiry ready to review and send.</p>
        <label for="product-enquiry-name">Your name</label>
        <input id="product-enquiry-name" name="name" type="text" autocomplete="name" maxlength="100" required>
        <label for="product-enquiry-phone">Phone number</label>
        <input id="product-enquiry-phone" name="phone" type="tel" autocomplete="tel" maxlength="30" required>
        <label for="product-enquiry-message">Your enquiry</label>
        <textarea id="product-enquiry-message" name="message" rows="4" maxlength="1000" placeholder="Ask about fitment, availability, or product details…" required></textarea>
        <p class="product-enquiry-privacy">Your message will be sent to Crystal Car Audio in WhatsApp. It is not saved on this website.</p>
        <button class="button button-dark product-enquiry-submit" type="submit">Continue to WhatsApp <span aria-hidden="true">↗</span></button>
    </form>
</dialog>

@if($relatedProducts->isNotEmpty())
<section class="product-collection related-collection"><div class="collection-heading"><div><p class="eyebrow">MORE FROM {{ strtoupper($product->category ?? 'CRYSTAL') }}</p><h2>Related products</h2><p>More gear that fits the same kind of setup.</p></div><div class="collection-actions"><a class="text-link" href="{{ $productCategoryUrl }}">View category ↗</a><button type="button" class="rail-button" data-scroll-rail="related" data-scroll-by="-1" aria-label="Scroll related products left">←</button><button type="button" class="rail-button" data-scroll-rail="related" data-scroll-by="1" aria-label="Scroll related products right">→</button></div></div><div class="product-rail" id="related">@foreach($relatedProducts as $related)@include('store.partials.product-card', ['product' => $related])@endforeach</div></section>
@endif
<section class="product-information" id="product-information">
    <nav class="product-information-nav" aria-label="Product information"><a href="#product-description">Description</a><a href="#product-specifications">Specifications</a><a href="#product-reviews">Reviews <span>({{ $reviews->count() }})</span></a></nav>
    <div class="product-information-grid">
        <article class="product-description-panel" id="product-description">
            <p class="eyebrow">PRODUCT DETAILS</p><h2>About {{ $product->name }}</h2>
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
<section class="product-category-cta" aria-label="Shop {{ $product->name }} from Crystal Car Audio">
    <div class="product-category-cta-copy">
        <p class="eyebrow">FROM CRYSTAL CAR AUDIO</p>
        <h2>Get {{ $product->name }}@if($product->category) in <a href="{{ $productCategoryUrl }}">{{ $product->category }}</a>@endif</h2>
        @if($product->brand)<p class="product-category-cta-brand">Brand: <strong>{{ $product->brand }}</strong></p>@endif
    </div>
    <div class="product-category-cta-buy">
        <span>Current price</span>
        <strong>KES {{ number_format($product->price) }}</strong>
        @if($discountPercent)<del>KES {{ number_format($product->original_price) }}</del>@endif
        <a class="button button-dark" href="#detail-purchase-form">Shop this product <span aria-hidden="true">→</span></a>
    </div>
</section>
<div class="product-action-dock"><button class="button button-light" type="submit" form="detail-purchase-form" name="buy_now" value="0" @if(!$inStock) disabled @endif>Add to bag</button><button class="button button-dark" type="submit" form="detail-purchase-form" name="buy_now" value="1" @if(!$inStock) disabled @endif>Buy now</button></div>
@endsection

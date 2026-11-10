@extends('store.layout')
@php
    $categoryImageForPreview = filled($categoryRecord?->image ?? null)
        ? \App\Support\MediaUrl::category($categoryRecord->image)
        : null;
    $firstProductImageForPreview = $products->first()?->image;
    $socialPreviewImage = $categoryImageForPreview
        ?: ($firstProductImageForPreview
            ? \App\Support\MediaUrl::url(str_starts_with($firstProductImageForPreview, 'http') ? $firstProductImageForPreview : 'uploads/'.ltrim($firstProductImageForPreview, '/'))
            : \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png'));
@endphp
@section('title', $seoTitle ?? $pageTitle.' | Crystal Car Audio')
@section('meta_description', $seoDescription ?? $pageDescription)
@section('meta_keywords', $seoKeywords ?? 'car audio Kenya, car radios Nairobi, car speakers, car accessories, Crystal Car Audio')
@section('meta_og_title', $seoOgTitle ?? ($seoTitle ?? $pageTitle.' | Crystal Car Audio'))
@section('meta_og_description', $seoOgDescription ?? ($seoDescription ?? $pageDescription))
@section('meta_image', $socialPreviewImage)
@section('content')
@php
    $breadcrumbCurrent = request()->routeIs('product-category', 'car-speakers') || filled(request('category'))
        ? $pageTitle
        : (request()->routeIs('special-offers') ? 'Special offers' : (request()->routeIs('product-tags') ? $pageTitle : 'All products'));
    $breadcrumbItems = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Products', 'url' => route('categories')],
        ['name' => $breadcrumbCurrent, 'url' => url()->current()],
    ];
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn ($item, $index) => [
            '@type' => 'ListItem', 'position' => $index + 1,
            'name' => $item['name'], 'item' => $item['url'],
        ], $breadcrumbItems, array_keys($breadcrumbItems)),
    ];
@endphp
<section class="page-hero collection-hero collection-hero-shop">
    <nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('categories') }}">Products</a></li><li aria-current="page">{{ $breadcrumbCurrent }}</li></ol></nav>
    <p class="eyebrow eyebrow-accent">{{ request()->routeIs('special-offers') ? 'CURRENT DEALS' : (request()->routeIs('product-tags') ? 'PRODUCT TAG' : 'THE CRYSTAL COLLECTION') }}</p>
    <h1>{{ $pageTitle }}</h1>
    <p class="collection-hero-description">{{ $pageDescription }}</p>
    @if(filled($categoryGuide ?? null))<details class="collection-category-guide"><summary>Read category guide</summary><p>{{ $categoryGuide }}</p></details>@endif
</section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<div class="collection-benefits" aria-label="Shopping benefits">
    <span><i aria-hidden="true">✓</i> Expert product advice</span>
    <span><i aria-hidden="true">↗</i> Delivery across Kenya</span>
    <span><i aria-hidden="true">◈</i> Secure checkout</span>
</div>
<section class="section listing listing-storefront collection-listing {{ request()->routeIs('special-offers') ? 'special-offers-listing' : '' }}">
    @unless(request()->routeIs('special-offers'))
    <aside class="category-filter collection-sidebar" aria-label="Filter products">
        <div class="collection-filter-title"><span>Filter products</span><a href="{{ url()->current() }}">Clear all</a></div>
        <details class="collection-filter-group" open>
            <summary>Shop by category <span aria-hidden="true">⌄</span></summary>
            <nav class="category-filter-links" aria-label="Browse products by category">
                <a href="{{ route('categories') }}" class="{{ request()->routeIs('categories') ? 'is-active' : '' }}" @if(request()->routeIs('categories')) aria-current="page" @endif><span>All products</span><span class="category-filter-arrow" aria-hidden="true">↗</span></a>
                @foreach($categories->reject(fn ($category) => in_array($category->slug, ['car-audio-system', 'car-audio-systems'], true)) as $category)
                    <a href="{{ $category->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $category->slug) }}" class="{{ request()->routeIs('product-category', 'car-speakers') && request()->route('slug') === $category->slug ? 'is-active' : '' }}" @if(request()->routeIs('product-category', 'car-speakers') && request()->route('slug') === $category->slug) aria-current="page" @endif><span>{{ $category->name }}</span><span class="category-filter-arrow" aria-hidden="true">↗</span></a>
                @endforeach
                <a href="{{ route('special-offers') }}" class="{{ request()->routeIs('special-offers') ? 'is-active' : '' }}" @if(request()->routeIs('special-offers')) aria-current="page" @endif><span>Special offers</span><span class="category-filter-arrow" aria-hidden="true">↗</span></a>
            </nav>
        </details>
        <form class="collection-price-filter" action="{{ url()->current() }}" method="get">
            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
            <details class="collection-filter-group" open>
                <summary>Shop by price <span aria-hidden="true">⌄</span></summary>
                <div class="price-filter-fields"><label>Minimum price<input type="number" min="0" step="100" name="min_price" value="{{ request('min_price') }}" placeholder="KES 0"></label><label>Maximum price<input type="number" min="0" step="100" name="max_price" value="{{ request('max_price') }}" placeholder="No limit"></label></div>
                <button type="submit" class="filter-apply">Apply price</button>
            </details>
            @if($brands->isNotEmpty())
                <details class="collection-filter-group">
                    <summary>Shop by brand <span aria-hidden="true">⌄</span></summary>
                    <label class="brand-filter-label" for="collection-brand">Choose a brand</label>
                    <select id="collection-brand" name="brand" onchange="this.form.requestSubmit()"><option value="">All brands</option>@foreach($brands as $brandName)<option value="{{ $brandName }}" @selected(request('brand') === $brandName)>{{ $brandName }}</option>@endforeach</select>
                </details>
            @endif
            <input type="hidden" name="sort" value="{{ request('sort', 'recommended') }}">
        </form>
    </aside>
    @endunless
    <div class="listing-main">
        <div class="listing-toolbar">
            <span class="listing-results"><b>{{ number_format($products->total()) }}</b> products</span>
            <div class="listing-toolbar-actions">
                <form class="listing-search" action="{{ route('search-results') }}"><input name="q" value="{{ request('q') }}" placeholder="Search products" aria-label="Search products"><button type="submit" aria-label="Search">⌕</button></form>
                <form class="listing-sort" action="{{ url()->current() }}" method="get">
                    @foreach(request()->except(['sort','page']) as $key => $value) @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
                    <label for="listing-sort">Sort by</label><select id="listing-sort" name="sort" onchange="this.form.requestSubmit()"><option value="recommended" @selected(request('sort', 'recommended') === 'recommended')>Recommended</option><option value="newest" @selected(request('sort') === 'newest')>Newest</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select>
                </form>
            </div>
        </div>
        @if($products->isNotEmpty())
            <div class="product-grid product-grid-storefront">@foreach($products as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div>
            <div class="pagination-wrap">{{ $products->onEachSide(1)->links('store.pagination') }}</div>
        @else
            <div class="empty-catalog"><span>◉</span><h3>No products found</h3><p>Try adjusting your filters or explore the full Crystal collection.</p><a href="{{ route('categories') }}" class="button button-dark">View all products <span aria-hidden="true">→</span></a></div>
        @endif
    </div>
</section>
<script>
(() => {
    if (!window.matchMedia('(max-width: 760px)').matches) return;
    document.querySelectorAll('.collection-price-filter > details[open]').forEach(group => group.removeAttribute('open'));
})();
</script>
@endsection

@extends('store.layout')
@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('meta_keywords', $seo['keywords'])
@section('meta_og_title', $seo['og_title'] ?: $seo['title'])
@section('meta_og_description', $seo['og_description'] ?: $seo['description'])
@section('content')
@php
    $breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop by brand', 'item' => route('shop-by-brand')],
    ]];
    $brandListSchema = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Car audio brands in Kenya', 'itemListElement' => $brands->values()->map(fn ($brand, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $brand->brand, 'url' => route('products.brand', \Illuminate\Support\Str::slug($brand->brand))])->all()];
@endphp
<section class="page-hero collection-hero collection-hero-shop">
    <nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Shop by brand</li></ol></nav>
    <p class="eyebrow eyebrow-accent">BRANDS WE TRUST</p><h1>Shop by brand</h1>
    <p class="collection-hero-description">Explore the car audio brands available at Crystal Car Audio. Select a brand to browse its current products.</p>
</section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($brandListSchema)</script>
<section class="section brand-directory-section">
    <div class="section-heading"><div><p class="eyebrow eyebrow-accent">THE CRYSTAL COLLECTION</p><h2>Browse available brands</h2><p>Find products by the names you know and trust.</p></div><span class="brand-directory-count">{{ $brands->count() }} brands</span></div>
    @if($brands->isNotEmpty())
        <div class="brand-directory-grid">
            @foreach($brands as $brand)
                <a class="brand-directory-card" href="{{ route('products.brand', \Illuminate\Support\Str::slug($brand->brand)) }}"><span class="brand-directory-initial" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brand->brand, 0, 1)) }}</span><span class="brand-directory-name">{{ $brand->brand }}</span><span class="brand-directory-products">{{ number_format((int) $brand->product_count) }} {{ \Illuminate\Support\Str::plural('product', (int) $brand->product_count) }}</span><span class="brand-directory-arrow" aria-hidden="true">↗</span></a>
            @endforeach
        </div>
    @else
        <div class="empty-catalog"><h2>Brands are being updated</h2><p>Browse all products while we add more brand listings.</p><a href="{{ route('categories') }}" class="button button-dark">View all products <span aria-hidden="true">→</span></a></div>
    @endif
</section>
@endsection

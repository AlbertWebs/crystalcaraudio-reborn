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
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop by category', 'item' => route('shop-by-category')],
    ]];
    $categoryListSchema = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Car audio product categories in Kenya', 'itemListElement' => $categories->values()->map(fn ($category, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $category->name, 'url' => route('product-category', $category->slug)])->all()];
@endphp
<section class="page-hero collection-hero collection-hero-shop">
    <nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Shop by category</li></ol></nav>
    <p class="eyebrow eyebrow-accent">FIND YOUR FREQUENCY</p><h1>Shop by category</h1>
    <p class="collection-hero-description">Start with the upgrade you have in mind. Browse every Crystal Car Audio category, then explore products selected for your drive.</p>
</section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($categoryListSchema)</script>
<section class="section category-directory-section">
    <div class="section-heading"><div><p class="eyebrow eyebrow-accent">THE CRYSTAL COLLECTION</p><h2>Find your next upgrade</h2><p>Choose a category to see available products, pricing and fitment details.</p></div><a class="text-link" href="{{ route('categories') }}">View all products <span aria-hidden="true">↗</span></a></div>
    <div class="category-directory-grid">
        @foreach($categories as $index => $category)
            <a class="category-directory-card" href="{{ route('product-category', $category->slug) }}">
                <div class="category-directory-art">
                    @if($category->image)<img src="{{ \App\Support\MediaUrl::category($category->image) }}" alt="{{ $category->name }}" loading="lazy">@else<span aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>@endif
                    <span class="category-directory-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="category-directory-copy"><div><h3>{{ $category->name }}</h3><p>{{ number_format((int) $category->product_count) }} {{ \Illuminate\Support\Str::plural('product', (int) $category->product_count) }}</p></div><span class="category-directory-arrow" aria-hidden="true">↗</span></div>
            </a>
        @endforeach
    </div>
</section>
@endsection

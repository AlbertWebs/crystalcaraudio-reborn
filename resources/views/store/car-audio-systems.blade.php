@extends('store.layout')
@php
    $previewProduct = $products->first();
    $previewImage = $previewProduct?->image
        ? \App\Support\MediaUrl::url(str_starts_with($previewProduct->image, 'http') ? $previewProduct->image : 'uploads/'.ltrim($previewProduct->image, '/'))
        : \App\Support\MediaUrl::category('images/categories/car-audio-system.webp');
@endphp
@section('title', 'Car Audio Systems in Nairobi, Kenya | Crystal Car Audio')
@section('meta_description', 'Shop car audio systems in Nairobi, Kenya. Explore complete sound upgrades with radios, speakers, amplifiers and subwoofers from Crystal Car Audio.')
@section('meta_keywords', 'car audio systems Kenya, car audio system Nairobi, complete car sound system, car audio upgrades, Crystal Car Audio')
@section('meta_og_title', 'Car Audio Systems in Nairobi, Kenya | Crystal Car Audio')
@section('meta_og_description', 'Explore complete car sound system upgrades, matched radios, speakers, amplifiers and subwoofers at Crystal Car Audio in Nairobi, Kenya.')
@section('meta_image', $previewImage)
@section('content')
@php
    $pageUrl = route('car-audio-systems');
    $breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Car audio systems', 'item' => $pageUrl],
    ]];
    $collectionSchema = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Car Audio Systems in Nairobi, Kenya', 'description' => 'Shop complete car audio system products and sound upgrades from Crystal Car Audio in Nairobi, Kenya.', 'url' => $pageUrl, 'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => $products->total(), 'itemListElement' => $products->getCollection()->values()->map(fn ($product, $index) => ['@type' => 'ListItem', 'position' => $products->firstItem() + $index, 'url' => route('product-single', $product->slug), 'name' => $product->name])->all()]];
@endphp
<section class="page-hero collection-hero collection-hero-shop">
    <nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Car audio systems</li></ol></nav>
    <p class="eyebrow eyebrow-accent">COMPLETE SOUND UPGRADES · NAIROBI, KENYA</p>
    <h1>Car Audio Systems in Nairobi &amp; Kenya</h1>
    <p class="collection-hero-description">Build a car sound system around the way you drive and listen. Explore complete audio products and get help matching radios, speakers, amplifiers and subwoofers to your vehicle.</p>
</section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($collectionSchema)</script>
<section class="section portfolio-section" aria-labelledby="system-guide-title">
    <div class="section-heading"><div><p class="eyebrow eyebrow-accent">PLAN YOUR CAR SOUND UPGRADE</p><h2 id="system-guide-title">Choose a balanced car audio system</h2><p>A good setup starts with equipment that works together and fits your vehicle. Browse the current system products below or explore individual components.</p></div></div>
    <div class="portfolio-grid portfolio-service-grid">
        <article class="portfolio-card"><div class="portfolio-card-copy"><h3>Start with a car radio</h3><p>Choose a head unit with the connectivity and controls you need, then confirm dashboard fitment.</p><a class="text-link" href="{{ route('product-category', 'car-radio') }}">Shop car radios ↗</a></div></article>
        <article class="portfolio-card"><div class="portfolio-card-copy"><h3>Match speakers and bass</h3><p>Door speakers, subwoofers and suitable amplification can bring clarity and fuller low frequencies to your setup.</p><a class="text-link" href="{{ route('car-speakers') }}">Shop car speakers ↗</a></div></article>
        <article class="portfolio-card"><div class="portfolio-card-copy"><h3>Get fitment advice</h3><p>Share your vehicle make, model and year with our team to check compatibility before you buy.</p><a class="text-link" href="{{ route('find-us') }}">Talk to Crystal Car Audio ↗</a></div></article>
    </div>
</section>
<section class="section listing listing-storefront collection-listing">
    <div class="listing-main">
        <div class="listing-toolbar"><span class="listing-results"><b>{{ number_format($products->total()) }}</b> car audio system products</span></div>
        @if($products->isNotEmpty())
            <div class="product-grid product-grid-storefront">@foreach($products as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div>
            <div class="pagination-wrap">{{ $products->links('store.pagination') }}</div>
        @else
            <div class="empty-catalog"><h2>Car audio system products coming soon</h2><p>Browse radios, speakers and amplifiers or contact Crystal Car Audio for help planning a complete setup.</p><a href="{{ route('categories') }}" class="button button-dark">Explore all products <span aria-hidden="true">→</span></a></div>
        @endif
    </div>
</section>
<section class="section portfolio-section portfolio-faq" aria-labelledby="car-audio-faq-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">CAR AUDIO SYSTEM GUIDE</p><h2 id="car-audio-faq-title">Car audio system questions</h2></div></div><div class="portfolio-faq-list">
    <details><summary>What does a complete car audio system include?</summary><p>A system can include a car radio, speakers, an amplifier and a subwoofer. The right combination depends on your vehicle, listening preferences and the equipment already installed.</p></details>
    <details><summary>Can I upgrade my car speakers without replacing the radio?</summary><p>Often, yes. Speaker size, wiring and power compatibility vary by vehicle, so check fitment before choosing replacement speakers.</p></details>
    <details><summary>Where can I buy car audio systems in Nairobi?</summary><p>Crystal Car Audio offers car audio products in Nairobi, Kenya. Contact our team for product advice, fitment questions and installation guidance.</p></details>
</div></section>
@endsection

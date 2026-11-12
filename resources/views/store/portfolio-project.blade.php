@extends('store.layout')
@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('meta_keywords', $seo['keywords'])
@section('meta_og_title', $seo['title'])
@section('meta_og_description', $seo['description'])
@php
    $portfolioImage = $project['images'][0] ?? null;
@endphp
@if($portfolioImage)
    @section('meta_image', $portfolioImage)
@endif
@section('content')
@php
    $images = $project['images'] ?? [];
    $vehicleDetails = trim(implode(' ', array_filter([
        $project['vehicle_make'] ?? null,
        $project['vehicle_model'] ?? null,
        $project['vehicle_year'] ?? null,
    ])));
    $videoUrl = $project['video_url'] ?? null;
    $breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our portfolio', 'item' => route('portfolio')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $project['title'], 'item' => route('folio', $project['slug'])],
    ]];
    $projectSchema = ['@context' => 'https://schema.org', '@type' => 'CreativeWork', 'name' => $project['title'], 'url' => route('folio', $project['slug']), 'description' => $seo['description'], 'image' => $images, 'about' => array_filter([$vehicleDetails, $project['upgrade_type'] ?? null]), 'contentLocation' => filled($project['service_area'] ?? null) ? ['@type' => 'Place', 'name' => $project['service_area']] : ['@type' => 'Place', 'name' => 'Nairobi, Kenya'], 'provider' => ['@type' => 'AutoRepair', 'name' => 'Crystal Car Audio', 'url' => route('home')]];
    if ($videoUrl) {
        $projectSchema['video'] = ['@type' => 'VideoObject', 'name' => $project['title'].' installation video', 'description' => $seo['description'], 'contentUrl' => $videoUrl];
        if (filled($project['project_date'] ?? null)) {
            $projectSchema['video']['uploadDate'] = $project['project_date'];
        }
    }
@endphp
<section class="portfolio-project-hero">
    <nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('portfolio') }}">Our portfolio</a></li><li aria-current="page">{{ $project['title'] }}</li></ol></nav>
    <p class="eyebrow eyebrow-accent">{{ $project['upgrade_type'] ?: 'CAR AUDIO INSTALLATION' }} · {{ $project['service_area'] ?: 'NAIROBI, KENYA' }}</p>
    <h1>{{ $project['title'] }}</h1>
    @if($vehicleDetails)<p class="portfolio-project-subtitle">Vehicle: {{ $vehicleDetails }}</p>@endif
</section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($projectSchema)</script>
<section class="section portfolio-project-content">
    <div class="portfolio-project-details" aria-label="Project details">
        @if($vehicleDetails)<article><small>VEHICLE</small><strong>{{ $vehicleDetails }}</strong></article>@endif
        @if(filled($project['work_order'] ?? null))<article><small>WORK ORDER</small><strong>{{ $project['work_order'] }}</strong></article>@endif
        @if(filled($project['upgrade_type'] ?? null))<article><small>TYPE OF UPGRADE</small><strong>{{ $project['upgrade_type'] }}</strong></article>@endif
        @if(filled($project['service_area'] ?? null))<article><small>INSTALLATION AREA</small><strong>{{ $project['service_area'] }}</strong></article>@endif
        @if(filled($project['project_date'] ?? null))<article><small>PROJECT DATE</small><strong>{{ \Illuminate\Support\Carbon::parse($project['project_date'])->format('F Y') }}</strong></article>@endif
    </div>
    @if($videoUrl)
        <section class="portfolio-project-video" aria-labelledby="portfolio-video-title"><h2 id="portfolio-video-title">Installation video</h2><video controls playsinline preload="metadata" @if($portfolioImage) poster="{{ $portfolioImage }}" @endif><source src="{{ $videoUrl }}" type="{{ str_ends_with(strtolower((string) $project['video_path']), '.webm') ? 'video/webm' : 'video/mp4' }}">Your browser does not support this video.</video></section>
    @endif
    @if(count($images))
        <section class="portfolio-project-gallery-section" aria-labelledby="portfolio-gallery-title"><h2 id="portfolio-gallery-title">Installation photos</h2><div class="portfolio-project-gallery">@foreach($images as $image)<figure><img src="{{ $image }}" alt="{{ $project['title'] }} car audio installation photo {{ $loop->iteration }}" loading="lazy" decoding="async"></figure>@endforeach</div></section>
    @endif
    <div class="portfolio-project-description"><h2>Upgrade overview</h2>{!! \Illuminate\Support\Str::markdown(trim((string) ($project['description'] ?? '')), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}@if(filled($project['client_name'] ?? null))<p><strong>Customer:</strong> {{ $project['client_name'] }}</p>@endif</div>
    @if(isset($installedProducts) && $installedProducts->isNotEmpty())
        <section class="portfolio-installed-products" aria-labelledby="installed-products-title">
            <p class="eyebrow eyebrow-accent">EQUIPMENT IN THIS INSTALLATION</p>
            <h2 id="installed-products-title">Products installed in this Toyota Axio</h2>
            <p>Explore the same models featured in this upgrade. Each link opens the product page for specifications, current availability and fitment support from Crystal Car Audio.</p>
            <div class="portfolio-installed-product-grid">
                @foreach($installedProducts as $installedProduct)
                    <article class="portfolio-installed-product">
                        <a class="portfolio-installed-product-image" href="{{ route('product-single', $installedProduct->slug) }}" aria-label="View {{ $installedProduct->name }}">
                            @if($installedProduct->image)
                                <img src="{{ \App\Support\MediaUrl::url('uploads/'.ltrim($installedProduct->image, '/')) }}" alt="{{ $installedProduct->name }} installed in the Toyota Axio audio upgrade" loading="lazy" decoding="async">
                            @else
                                <span aria-hidden="true">{{ $installedProduct->brand }}</span>
                            @endif
                        </a>
                        <div><small>{{ $installedProduct->brand }} · {{ $installedProduct->category }}</small><h3><a href="{{ route('product-single', $installedProduct->slug) }}">{{ $installedProduct->name }}</a></h3><a class="text-link" href="{{ route('product-single', $installedProduct->slug) }}">View product details <span aria-hidden="true">↗</span></a></div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    <a class="text-link" href="{{ route('portfolio') }}">← Back to our portfolio</a>
</section>
@endsection

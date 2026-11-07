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
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our portfolio', 'item' => route('portfolio')],
    ]];
@endphp
<section class="page-hero collection-hero collection-hero-shop"><nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Our portfolio</li></ol></nav><p class="eyebrow eyebrow-accent">MADE FOR THE ROAD</p><h1>Our installation portfolio</h1><p class="collection-hero-description">See car audio upgrades completed by the Crystal Car Audio team in Nairobi and across Kenya.</p></section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<section class="section portfolio-section"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">RECENT PROJECTS</p><h2>Work built around every drive</h2><p>Explore vehicle upgrades, audio installations and multimedia fitment projects.</p></div></div>
    @if($projects->isNotEmpty())<div class="portfolio-grid">@foreach($projects as $project)<a class="portfolio-card" href="{{ route('folio', $project['id']) }}"><div class="portfolio-card-image">@if(!empty($project['images']))<img src="{{ $project['images'][0] }}" alt="{{ $project['title'] }}" loading="lazy">@else<span aria-hidden="true">CCA</span>@endif<span class="portfolio-card-open" aria-hidden="true">↗</span></div><div class="portfolio-card-copy"><p class="eyebrow eyebrow-accent">{{ filled($project['service'] ?? null) && !is_numeric($project['service']) ? $project['service'] : 'CRYSTAL INSTALLATION' }}</p><h3>{{ $project['title'] }}</h3><p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) ($project['content'] ?? 'A Crystal Car Audio installation project in Kenya.'))), 150) }}</p><span class="portfolio-card-link">View project <b aria-hidden="true">→</b></span></div></a>@endforeach</div>@else<div class="empty-catalog"><h2>Projects coming soon</h2><p>Our installation portfolio is being refreshed. Contact us to discuss your setup.</p><a href="/find-us" class="button button-dark">Talk to our team <span aria-hidden="true">→</span></a></div>@endif
</section>
@endsection

@extends('store.layout')
@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('meta_keywords', $seo['keywords'])
@section('meta_og_title', $seo['og_title'] ?: $seo['title'])
@section('meta_og_description', $seo['og_description'] ?: $seo['description'])
@php($portfolioPreviewImage = $projects->first()['images'][0] ?? null)
@if($portfolioPreviewImage)@section('meta_image', $portfolioPreviewImage)@endif
@section('content')
@php
    $breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our portfolio', 'item' => route('portfolio')],
    ]];
    $projectQuestions = [
        ['question' => 'What car audio installation projects does Crystal Car Audio handle?', 'answer' => 'Our portfolio includes Android radio and multimedia upgrades, reverse cameras, door speakers, amplifiers, tweeters and subwoofers. We recommend equipment to suit your vehicle and listening goals.'],
        ['question' => 'Can you install an Android car radio with a reverse camera?', 'answer' => 'Yes. Our past projects include Android radio installations paired with reverse cameras. Contact our team with your vehicle make, model and year so we can check fitment.'],
        ['question' => 'Do you install car speakers and subwoofers in Nairobi?', 'answer' => 'Yes. We install speakers and subwoofers, including under-seat options, and can help match them with a suitable radio and amplifier.'],
        ['question' => 'Where does Crystal Car Audio carry out installations?', 'answer' => 'Crystal Car Audio is based in Nairobi and serves drivers across Kenya. Past portfolio projects include vehicle upgrades in areas such as Parklands and Westlands.'],
    ];
    $portfolioUrl = route('portfolio');
    $portfolioSchema = ['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'CollectionPage', '@id' => $portfolioUrl.'#page', 'url' => $portfolioUrl, 'name' => $seo['title'], 'description' => $seo['description'], 'mainEntity' => ['@id' => $portfolioUrl.'#projects']],
        ['@type' => 'ItemList', '@id' => $portfolioUrl.'#projects', 'name' => 'Car audio installation projects in Nairobi, Kenya', 'numberOfItems' => $projects->count(), 'itemListElement' => $projects->values()->map(fn ($project, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'url' => route('folio', $project['id']), 'item' => array_filter(['@type' => 'CreativeWork', 'name' => $project['title'], 'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) ($project['content'] ?? ''))), 300), 'image' => $project['images'][0] ?? null, 'url' => route('folio', $project['id'])])])->all()],
    ]];
    $faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($projectQuestions)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()];
@endphp
<section class="page-hero collection-hero collection-hero-shop"><nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Our portfolio</li></ol></nav><p class="eyebrow eyebrow-accent">CAR AUDIO INSTALLATION IN NAIROBI</p><h1>Car Audio Installation Portfolio</h1><p class="collection-hero-description">Explore real Crystal Car Audio upgrades in Nairobi, from Android radio and reverse camera fitment to speaker, amplifier and subwoofer installations.</p></section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($portfolioSchema)</script>
<script type="application/ld+json">@json($faqSchema)</script>
<section class="section portfolio-section" aria-labelledby="portfolio-projects-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">COMPLETED CUSTOMER PROJECTS</p><h2 id="portfolio-projects-title">Car audio upgrades built around each vehicle</h2><p>See installation photos, equipment choices and upgrade details from Crystal Car Audio projects in Nairobi.</p></div></div>
    @if($projects->isNotEmpty())<div class="portfolio-grid">@foreach($projects as $project)<a class="portfolio-card" href="{{ route('folio', $project['id']) }}"><div class="portfolio-card-image">@if(!empty($project['images']))<img src="{{ $project['images'][0] }}" alt="Car audio installation in Nairobi: {{ $project['title'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">CCA</span>@endif<span class="portfolio-card-open" aria-hidden="true">↗</span></div><div class="portfolio-card-copy"><p class="eyebrow eyebrow-accent">{{ filled($project['service'] ?? null) && !is_numeric($project['service']) ? $project['service'] : 'CAR AUDIO INSTALLATION' }}</p><h3>{{ $project['title'] }}</h3><p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) ($project['content'] ?? 'Car audio installation by Crystal Car Audio in Nairobi, Kenya.'))), 170) }}</p><span class="portfolio-card-link">View project <b aria-hidden="true">→</b></span></div></a>@endforeach</div>@else<div class="empty-catalog"><h2>Projects coming soon</h2><p>Our installation portfolio is being refreshed. Contact us to discuss your setup.</p><a href="/find-us" class="button button-dark">Talk to our team <span aria-hidden="true">→</span></a></div>@endif
</section>
<section class="section portfolio-section" aria-labelledby="portfolio-services-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">RADIO, SPEAKER &amp; MULTIMEDIA UPGRADES</p><h2 id="portfolio-services-title">Car audio installation services in Nairobi</h2><p>Crystal Car Audio plans each upgrade around the vehicle, the equipment and the sound the driver wants.</p></div></div><div class="portfolio-grid portfolio-service-grid"><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Android radio and reverse camera installation</h3><p>Upgrade to a modern in-dash system with navigation, smartphone connectivity and a reverse camera, with fitment checked for your vehicle.</p></div></article><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Car speaker and amplifier upgrades</h3><p>Improve clarity and output with well-matched door speakers, tweeters and an amplifier selected for the system.</p></div></article><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Subwoofer and full-system installation</h3><p>Add deeper bass with an under-seat subwoofer or plan a complete system upgrade with radio, speakers and amplification.</p></div></article></div></section>
<section class="section portfolio-section portfolio-faq" aria-labelledby="portfolio-faq-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">PLAN YOUR UPGRADE</p><h2 id="portfolio-faq-title">Car audio installation questions</h2></div></div><div class="portfolio-faq-list">@foreach($projectQuestions as $item)<details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>@endforeach</div></section>
@endsection

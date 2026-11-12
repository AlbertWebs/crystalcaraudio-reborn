@extends('store.layout')
@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('meta_keywords', $seo['keywords'])
@section('meta_og_title', $seo['og_title'] ?: $seo['title'])
@section('meta_og_description', $seo['og_description'] ?: $seo['description'])
@php
    $portfolioPreviewImage = $projects->first()['images'][0] ?? null;
@endphp
@if($portfolioPreviewImage)
    @section('meta_image', $portfolioPreviewImage)
@endif
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
        ['@type' => 'ItemList', '@id' => $portfolioUrl.'#projects', 'name' => 'Car audio installation projects in Nairobi, Kenya', 'numberOfItems' => $projects->count(), 'itemListElement' => $projects->values()->map(fn ($project, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'url' => route('folio', $project['slug']), 'item' => array_filter(['@type' => 'CreativeWork', 'name' => $project['title'], 'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) ($project['content'] ?? ''))), 300), 'image' => $project['images'][0] ?? null, 'url' => route('folio', $project['slug'])])])->all()],
    ]];
    $faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($projectQuestions)->map(fn ($item) => ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']]])->all()];
@endphp
<section class="page-hero collection-hero collection-hero-shop"><nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li aria-current="page">Our portfolio</li></ol></nav><p class="eyebrow eyebrow-accent">CAR AUDIO INSTALLATION IN NAIROBI</p><h1>Car Audio Installation Portfolio</h1><p class="collection-hero-description">Explore real Crystal Car Audio upgrades in Nairobi, from Android radio and reverse camera fitment to speaker, amplifier and subwoofer installations.</p><div class="collection-hero-actions"><a class="button button-dark" href="#request-installation">Request an installation <span aria-hidden="true">→</span></a></div></section>
<script type="application/ld+json">@json($breadcrumbSchema)</script>
<script type="application/ld+json">@json($portfolioSchema)</script>
<script type="application/ld+json">@json($faqSchema)</script>
<section class="section portfolio-section" aria-labelledby="portfolio-projects-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">COMPLETED CUSTOMER PROJECTS</p><h2 id="portfolio-projects-title">Car audio upgrades built around each vehicle</h2><p>See installation photos, equipment choices and upgrade details from Crystal Car Audio projects in Nairobi.</p></div></div>
    @if($projects->isNotEmpty())<div class="portfolio-grid">@foreach($projects as $project)<a class="portfolio-card" href="{{ route('folio', $project['slug']) }}"><div class="portfolio-card-image">@if(!empty($project['images']))<img src="{{ $project['images'][0] }}" alt="Car audio installation in Nairobi: {{ $project['title'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">CCA</span>@endif<span class="portfolio-card-open" aria-hidden="true">↗</span></div><div class="portfolio-card-copy"><p class="eyebrow eyebrow-accent">{{ filled($project['service'] ?? null) && !is_numeric($project['service']) ? $project['service'] : 'CAR AUDIO INSTALLATION' }}</p><h3>{{ $project['title'] }}</h3><p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) ($project['content'] ?? 'Car audio installation by Crystal Car Audio in Nairobi, Kenya.'))), 170) }}</p><span class="portfolio-card-link">View project <b aria-hidden="true">→</b></span></div></a>@endforeach</div>@else<div class="empty-catalog"><h2>Projects coming soon</h2><p>Our installation portfolio is being refreshed. Contact us to discuss your setup.</p><a href="/find-us" class="button button-dark">Talk to our team <span aria-hidden="true">→</span></a></div>@endif
</section>
<section class="section portfolio-section portfolio-request-section" id="request-installation" aria-labelledby="portfolio-request-title">
    <div class="portfolio-request-card">
        <div class="portfolio-request-heading"><p class="eyebrow eyebrow-accent">PLAN YOUR UPGRADE</p><h2 id="portfolio-request-title">Request an installation</h2><p>Tell us about your car and the upgrade you have in mind. Our team will check fitment and contact you to agree on a time and quote.</p></div>
        <form class="portfolio-request-form" id="portfolio-installation-form" action="{{ route('portfolio.installation-requests.store') }}" method="post" data-installation-request-form>
            @csrf
            <div class="portfolio-request-trap" aria-hidden="true"><label for="portfolio-company-website">Leave this field empty</label><input id="portfolio-company-website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>
            <div class="portfolio-request-grid">
                <label>Your name <span aria-hidden="true">*</span><input name="customer_name" type="text" autocomplete="name" maxlength="190" required></label>
                <label>Email address <span aria-hidden="true">*</span><input name="email" type="email" autocomplete="email" maxlength="190" required></label>
                <label>Phone number <span aria-hidden="true">*</span><input name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="e.g. 0712 345 678" maxlength="40" required></label>
                <label>Vehicle make <span aria-hidden="true">*</span><input name="vehicle_make" type="text" autocomplete="off" placeholder="e.g. Toyota" maxlength="100" required></label>
                <label>Vehicle model <span aria-hidden="true">*</span><input name="vehicle_model" type="text" autocomplete="off" placeholder="e.g. Fielder" maxlength="120" required></label>
                <label>Vehicle year <span class="portfolio-request-optional">Optional</span><input name="vehicle_year" type="number" min="1950" max="{{ now()->year + 1 }}" inputmode="numeric" placeholder="e.g. 2018"></label>
                <label>What would you like installed? <span aria-hidden="true">*</span><select name="service" required><option value="">Choose an upgrade</option><option>Android radio upgrade</option><option>Car speaker or subwoofer upgrade</option><option>Amplifier installation</option><option>Reverse camera or dashcam installation</option><option>Alarm or parking sensor installation</option><option>Other</option></select></label>
                <label class="portfolio-request-service-detail" data-service-detail-wrapper hidden>Tell us about the upgrade <span aria-hidden="true">*</span><input name="service_detail" type="text" maxlength="140" disabled placeholder="What would you like us to install?"></label>
                <label>Preferred date and time <span class="portfolio-request-optional">Optional</span><input name="preferred_at" type="datetime-local" min="{{ now()->format('Y-m-d\\TH:i') }}"></label>
                <label>Preferred location <span class="portfolio-request-optional">Optional</span><input name="location" type="text" maxlength="190" placeholder="e.g. Nairobi or nearby area"></label>
                <label class="portfolio-request-wide">Anything else we should know? <span class="portfolio-request-optional">Optional</span><textarea name="notes" rows="4" maxlength="3000" placeholder="Share your goals, questions or any fitment details."></textarea></label>
            </div>
            <div class="portfolio-request-feedback" data-installation-feedback role="status" aria-live="polite" tabindex="-1" hidden></div>
            <div class="portfolio-request-submit"><p>We’ll use these details only to respond to your installation request.</p><button class="button button-dark" type="submit">Send installation request <span aria-hidden="true">→</span></button></div>
        </form>
    </div>
</section>
<section class="section portfolio-section" aria-labelledby="portfolio-services-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">RADIO, SPEAKER &amp; MULTIMEDIA UPGRADES</p><h2 id="portfolio-services-title">Car audio installation services in Nairobi</h2><p>Crystal Car Audio plans each upgrade around the vehicle, the equipment and the sound the driver wants.</p></div></div><div class="portfolio-grid portfolio-service-grid"><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Android radio and reverse camera installation</h3><p>Upgrade to a modern in-dash system with navigation, smartphone connectivity and a reverse camera, with fitment checked for your vehicle.</p></div></article><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Car speaker and amplifier upgrades</h3><p>Improve clarity and output with well-matched door speakers, tweeters and an amplifier selected for the system.</p></div></article><article class="portfolio-card"><div class="portfolio-card-copy"><h3>Subwoofer and full-system installation</h3><p>Add deeper bass with an under-seat subwoofer or plan a complete system upgrade with radio, speakers and amplification.</p></div></article></div></section>
<section class="section portfolio-section portfolio-faq" aria-labelledby="portfolio-faq-title"><div class="section-heading"><div><p class="eyebrow eyebrow-accent">PLAN YOUR UPGRADE</p><h2 id="portfolio-faq-title">Car audio installation questions</h2></div></div><div class="portfolio-faq-list">@foreach($projectQuestions as $item)<details><summary>{{ $item['question'] }}</summary><p>{{ $item['answer'] }}</p></details>@endforeach</div></section>
@endsection

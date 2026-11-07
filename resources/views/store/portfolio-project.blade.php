@extends('store.layout')
@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('meta_keywords', $seo['keywords'])
@section('meta_og_title', $seo['title'])
@section('meta_og_description', $seo['description'])
@if(!empty($project['images'][0]))@section('meta_image', $project['images'][0])@endif
@section('content')
@php $images = $project['images'] ?? []; @endphp
<section class="portfolio-project-hero"><nav class="collection-breadcrumbs" aria-label="Breadcrumb"><ol><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('portfolio') }}">Our portfolio</a></li><li aria-current="page">{{ $project['title'] }}</li></ol></nav><p class="eyebrow eyebrow-accent">CRYSTAL INSTALLATION · {{ $project['date'] ?? 'NAIROBI, KENYA' }}</p><h1>{{ $project['title'] }}</h1>@if(filled($project['service'] ?? null) && !is_numeric($project['service']))<p>{{ $project['service'] }}</p>@endif</section>
<section class="section portfolio-project-content">@if(count($images))<div class="portfolio-project-gallery">@foreach($images as $image)<figure><img src="{{ $image }}" alt="{{ $project['title'] }} — installation photo {{ $loop->iteration }}" loading="lazy"></figure>@endforeach</div>@endif<div class="portfolio-project-description">{!! nl2br(e(trim(strip_tags((string) ($project['content'] ?? ''))))) !!}@if(filled($project['client'] ?? null))<p><strong>Client:</strong> {{ $project['client'] }}</p>@endif</div><a class="text-link" href="{{ route('portfolio') }}">← Back to our portfolio</a></section>
@endsection

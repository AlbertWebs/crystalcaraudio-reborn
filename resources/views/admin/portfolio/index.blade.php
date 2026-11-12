@extends('admin.layout')
@section('title', 'Installation portfolio | Admin')
@section('admin_page_title', 'Installation portfolio')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-catalogue admin-portfolio-page">
    <div class="admin-page-heading"><div><p class="eyebrow">CUSTOMER PROJECTS</p><h1>Installation portfolio</h1><p>Edit the legacy projects or add a new vehicle upgrade with photos and video.</p></div><a class="admin-primary-action" href="{{ route('admin.portfolio.create') }}">Add project <span aria-hidden="true">＋</span></a></div>
    @if(session('success'))<div class="catalogue-alert" role="status">{{ session('success') }}</div>@endif
    @if($projects->isNotEmpty())
        <div class="admin-portfolio-grid">
            @foreach($projects as $project)
                <article class="admin-portfolio-card">
                    <div class="admin-portfolio-card-image">
                        @if(!empty($project['images'][0]))<img src="{{ $project['images'][0] }}" alt="{{ $project['title'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">CCA</span>@endif
                        <span class="admin-portfolio-status {{ $project['is_published'] ? 'is-published' : '' }}">{{ $project['is_published'] ? 'Published' : 'Draft' }}</span>
                    </div>
                    <div class="admin-portfolio-card-copy">
                        <p class="eyebrow">{{ trim(($project['vehicle_make'] ?? '').' '.($project['vehicle_model'] ?? '').' '.($project['vehicle_year'] ?? '')) ?: 'VEHICLE DETAILS NEEDED' }}</p>
                        <h2>{{ $project['title'] }}</h2>
                        <p>{{ $project['upgrade_type'] ?: 'Upgrade type not set' }}@if($project['work_order']) · Work order {{ $project['work_order'] }}@endif</p>
                        <div class="admin-portfolio-card-meta"><span>{{ count($project['image_paths']) }} {{ \Illuminate\Support\Str::plural('photo', count($project['image_paths'])) }}</span><span>{{ $project['video_path'] ? 'Video added' : 'No video' }}</span><span>{{ count($project['related_product_slugs'] ?? []) }} product links</span><span>{{ count($project['related_category_slugs'] ?? []) }} category links</span></div>
                        <div class="admin-portfolio-card-actions"><a class="admin-secondary-action" href="{{ route('folio', $project['slug']) }}" target="_blank" rel="noopener">View page <span aria-hidden="true">↗</span></a><a class="admin-secondary-action" href="{{ route('admin.portfolio.edit', $project['admin_key']) }}">Edit project <span aria-hidden="true">→</span></a></div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <section class="admin-panel"><h2>No portfolio projects yet</h2><p>Add the first installation project with its vehicle details, work order, upgrade type, photos and video.</p><a class="admin-primary-action" href="{{ route('admin.portfolio.create') }}">Add project</a></section>
    @endif
</div>
<style>
    .admin-portfolio-card-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
    .admin-portfolio-card-meta{display:flex;flex-wrap:wrap;gap:8px}
    .admin-portfolio-card-meta span{padding:4px 7px;border-radius:20px;background:#f2f6f3;color:#647269;font-size:10px}
</style>
@endsection

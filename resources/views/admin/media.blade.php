@extends('admin.layout')
@section('title', 'Banners & backgrounds | Admin')
@section('admin_page_title', 'Banners & backgrounds')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-media-page">
    <div class="admin-page-heading"><div><p class="eyebrow">STOREFRONT APPEARANCE</p><h1>Banners &amp; backgrounds</h1><p>Update the visuals used across the homepage and footer. New files replace the current asset wherever it appears.</p></div></div>
    @if($errors->any())<div class="catalogue-alert" role="alert"><strong>Upload could not be saved.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="admin-media-grid">
        @foreach($assets as $asset)
            <article class="admin-media-card">
                <div class="admin-media-preview {{ $asset->type === 'video' ? 'is-video' : '' }}">
                    @if($asset->type === 'video')<video src="{{ $asset->url }}" muted loop playsinline controls preload="metadata" aria-label="Current footer background video"></video>
                    @else<img src="{{ $asset->url }}" alt="Current {{ $asset->label }}" loading="lazy" decoding="async">@endif
                    <span class="admin-media-type">{{ strtoupper($asset->type) }}</span>
                </div>
                <div class="admin-media-copy"><h2>{{ $asset->label }}</h2><p>{{ $asset->description }}</p><small>Recommended: {{ $asset->recommended_size }}</small><code>{{ $asset->path }}</code></div>
                <form class="admin-media-upload" method="post" action="{{ route('admin.media.update', $asset->asset_key) }}" enctype="multipart/form-data" data-media-upload>
                    @csrf @method('PUT')
                    <label class="admin-media-dropzone"><input type="file" name="file" accept="{{ $asset->type === 'video' ? 'video/mp4' : 'image/jpeg,image/png,image/webp' }}" required data-media-file><span><b aria-hidden="true">↑</b><strong data-media-label>Choose a {{ $asset->type }}</strong><small>Max 20 MB</small></span></label>
                    <button class="admin-primary-action" type="submit">Upload and replace <span aria-hidden="true">→</span></button>
                </form>
            </article>
        @endforeach
    </div>
</div>
<script>
document.querySelectorAll('[data-media-upload]').forEach(form => {
    const input = form.querySelector('[data-media-file]');
    const label = form.querySelector('[data-media-label]');
    input.addEventListener('change', () => { label.textContent = input.files?.[0]?.name || 'Choose a file'; });
});
</script>
@endsection

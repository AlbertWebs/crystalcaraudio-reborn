@extends('admin.layout')
@section('title', ($editing ? 'Edit portfolio project' : 'Add portfolio project').' | Admin')
@section('admin_page_title', $editing ? 'Edit portfolio project' : 'Add portfolio project')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-catalogue admin-portfolio-page">
    <div class="admin-page-heading"><div><p class="eyebrow">INSTALLATION PORTFOLIO</p><h1>{{ $editing ? 'Edit project details' : 'Add a project' }}</h1><p>Use a descriptive URL slug and complete vehicle and upgrade details so every project has its own shareable page.</p></div><a class="admin-secondary-action" href="{{ route('admin.portfolio') }}">← Back to projects</a></div>
    @if($errors->any())<div class="catalogue-alert" role="alert"><strong>Please review the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="catalogue-product-form standalone-product-form" method="post" action="{{ $editing ? route('admin.portfolio.update', $project['admin_key']) : route('admin.portfolio.store') }}" enctype="multipart/form-data">
        @csrf @if($editing)@method('PATCH')@endif
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">01</span><div><h2>Project and vehicle</h2><p>These details appear on the project page and in search results.</p></div></div>
            <div class="catalogue-form-grid">
                <label class="catalogue-field catalogue-field-wide">Project title<input required name="title" value="{{ old('title', $project['title']) }}" maxlength="190" data-portfolio-title placeholder="e.g. Toyota Axio full system upgrade"></label>
                <label class="catalogue-field catalogue-field-wide">Page URL slug<input required name="slug" value="{{ old('slug', $project['slug']) }}" maxlength="190" pattern="[a-z0-9]+(-[a-z0-9]+)*" data-portfolio-slug><small>Public URL: /our-portfolio/{{ old('slug', $project['slug']) }}</small></label>
                <label class="catalogue-field">Vehicle make<input required name="vehicle_make" value="{{ old('vehicle_make', $project['vehicle_make']) }}" maxlength="100" placeholder="Toyota"></label>
                <label class="catalogue-field">Vehicle model<input required name="vehicle_model" value="{{ old('vehicle_model', $project['vehicle_model']) }}" maxlength="160" placeholder="Axio"></label>
                <label class="catalogue-field">Vehicle year<input type="number" name="vehicle_year" value="{{ old('vehicle_year', $project['vehicle_year']) }}" min="1950" max="{{ now()->year + 1 }}"></label>
                <label class="catalogue-field">Work order<input name="work_order" value="{{ old('work_order', $project['work_order']) }}" maxlength="100" placeholder="e.g. CCA-2026-014"></label>
                <label class="catalogue-field catalogue-field-wide">Type of upgrade<input required name="upgrade_type" value="{{ old('upgrade_type', $project['upgrade_type']) }}" maxlength="190" placeholder="e.g. Android radio and reverse camera installation"></label>
                <label class="catalogue-field">Project date<input type="date" name="project_date" value="{{ old('project_date', $project['project_date']) }}"></label>
                <label class="catalogue-field">Service area<input name="service_area" value="{{ old('service_area', $project['service_area']) }}" maxlength="190" placeholder="e.g. Westlands, Nairobi"></label>
                <label class="catalogue-field">Client name <small>Optional</small><input name="client_name" value="{{ old('client_name', $project['client_name']) }}" maxlength="190"></label>
                <label class="catalogue-field catalogue-field-wide">Project description<textarea name="description" rows="7" required maxlength="50000" placeholder="Describe the customer’s needs, equipment fitted and work completed.">{{ old('description', $project['description']) }}</textarea><small>Write a clear project summary. Avoid publishing personal contact details.</small></label>
            </div>
        </section>
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">02</span><div><h2>Photos and video</h2><p>Keep the existing legacy photos, add more images, or upload an installation video.</p></div></div>
            @if(!empty($project['image_paths']))
                <div class="admin-portfolio-existing-images">
                    @foreach($project['image_paths'] as $index => $imagePath)
                        <label><img src="{{ $project['images'][$index] ?? '' }}" alt="Existing project photo {{ $index + 1 }}" loading="lazy"><span><input type="checkbox" name="keep_images[]" value="{{ $imagePath }}" @checked(in_array($imagePath, old('keep_images', $project['image_paths']), true))> Keep photo {{ $index + 1 }}</span></label>
                    @endforeach
                </div>
            @endif
            <div class="catalogue-form-grid">
                <label class="catalogue-field catalogue-field-wide">Add project photos<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple><small>JPG, PNG or WebP. Up to 8 new photos, maximum 5 MB each.</small></label>
                <label class="catalogue-field catalogue-field-wide">Upload project video<input type="file" name="video" accept="video/mp4,video/webm"><small>MP4 or WebM, maximum 100 MB. Videos are shown with controls on the public project page.</small></label>
                @if($project['video_url'])<div class="catalogue-field catalogue-field-wide"><span>Current project video</span><video class="admin-portfolio-video-preview" src="{{ $project['video_url'] }}" controls preload="metadata"></video><label><input type="checkbox" name="remove_video" value="1"> Remove current video</label></div>@endif
            </div>
        </section>
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">03</span><div><h2>Visibility</h2><p>Draft projects stay out of the public portfolio and sitemap.</p></div></div>
            <label class="portfolio-publish-toggle"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project['is_published']))><span><strong>Publish this project</strong><small>Show it on the portfolio page and allow search engines to index its URL.</small></span></label>
        </section>
        <div class="catalogue-form-actions"><a class="admin-secondary-action" href="{{ route('admin.portfolio') }}">Cancel</a><button class="admin-primary-action" type="submit">{{ $editing ? 'Save project' : 'Create project' }} <span aria-hidden="true">→</span></button></div>
    </form>
</div>
<script>
(() => {
    const title = document.querySelector('[data-portfolio-title]');
    const slug = document.querySelector('[data-portfolio-slug]');
    if (!title || !slug) return;
    let slugEdited = false;
    slug.addEventListener('input', () => { slugEdited = true; });
    title.addEventListener('input', () => {
        if (!slugEdited) slug.value = title.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });
})();
</script>
@endsection

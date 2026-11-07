@extends('admin.layout')
@section('title', 'SEO management | Crystal Car Audio')
@section('admin_page_title', 'SEO management')
@section('admin_content')
<div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE MANAGEMENT</p><h1>Search appearance</h1><p>Manage page titles, search descriptions, keywords and social sharing metadata for the key storefront landing pages.</p></div></div>
<form method="post" action="{{ route('admin.seo.update') }}" class="admin-seo-form">@csrf @method('put')
    @foreach($pages as $page)
        <section class="admin-panel admin-seo-panel"><div class="admin-panel-heading"><div><p class="admin-kicker">{{ $page['path'] }}</p><h2>{{ $page['label'] }}</h2></div><a href="{{ $page['path'] === '/products/{category}' || $page['path'] === '/products/brand/{brand}' ? '#' : $page['path'] }}" target="_blank" rel="noopener">Preview ↗</a></div>
            <div class="admin-seo-fields">
                <label>SEO title<input name="pages[{{ $page['key'] }}][title]" value="{{ old('pages.'.$page['key'].'.title', $page['title']) }}" maxlength="255" required>@error('pages.'.$page['key'].'.title')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label>Meta description<textarea name="pages[{{ $page['key'] }}][description]" rows="3" maxlength="320" required>{{ old('pages.'.$page['key'].'.description', $page['description']) }}</textarea><small>Use {category} or {brand} for the page template.</small>@error('pages.'.$page['key'].'.description')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label>Keywords<input name="pages[{{ $page['key'] }}][keywords]" value="{{ old('pages.'.$page['key'].'.keywords', $page['keywords']) }}" maxlength="500"></label>
                <label>Open Graph title<input name="pages[{{ $page['key'] }}][og_title]" value="{{ old('pages.'.$page['key'].'.og_title', $page['og_title']) }}" maxlength="255"></label>
                <label>Open Graph description<textarea name="pages[{{ $page['key'] }}][og_description]" rows="2" maxlength="320">{{ old('pages.'.$page['key'].'.og_description', $page['og_description']) }}</textarea></label>
            </div>
        </section>
    @endforeach
    <div class="admin-form-actions"><button class="admin-button admin-button-primary" type="submit">Save SEO settings</button></div>
</form>
@endsection

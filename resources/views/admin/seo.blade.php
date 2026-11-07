@extends('admin.layout')
@section('title', 'SEO management | Crystal Car Audio')
@section('admin_page_title', 'SEO management')
@section('admin_content')
<div class="admin-seo-page">
    <header class="admin-seo-intro">
        <div>
            <p class="admin-kicker">WEBSITE MANAGEMENT</p>
            <h1>Search appearance</h1>
            <p>Shape how key Crystal Car Audio pages appear in search results and when shared.</p>
        </div>
        <div class="admin-seo-intro-note"><span aria-hidden="true">⌕</span><div><strong>{{ $pages->count() }} page templates</strong><small>One save updates all sections below.</small></div></div>
    </header>

    <nav class="admin-seo-jump" aria-label="Jump to SEO page">
        <span>EDIT PAGE</span>
        @foreach($pages as $page)
            <a href="#seo-{{ $page['key'] }}">{{ $page['label'] }}</a>
        @endforeach
    </nav>

    <form method="post" action="{{ route('admin.seo.update') }}" class="admin-seo-form admin-seo-editor-form">@csrf @method('put')
        @foreach($pages as $page)
            @php
                $previewUrl = match ($page['key']) {
                    'category_detail' => route('product-category', 'car-radio'),
                    'brand_detail' => route('products.brand', 'Pioneer'),
                    default => url($page['path']),
                };
            @endphp
            <section class="admin-panel admin-seo-panel admin-seo-editor-card" id="seo-{{ $page['key'] }}" data-seo-editor>
                <div class="admin-seo-card-heading">
                    <span class="admin-seo-card-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div><p class="admin-kicker">{{ $page['path'] }}</p><h2>{{ $page['label'] }}</h2></div>
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="admin-seo-preview-link">Preview page <span aria-hidden="true">↗</span></a>
                </div>

                <div class="admin-seo-editor-grid">
                    <div class="admin-seo-main-fields">
                        <label class="admin-seo-field">
                            <span>SEO title <small>Recommended: 50–60 characters</small></span>
                            <input data-seo-title name="pages[{{ $page['key'] }}][title]" value="{{ old('pages.'.$page['key'].'.title', $page['title']) }}" maxlength="255" required>
                            <small class="admin-seo-count" data-seo-title-count aria-live="polite"></small>
                            @error('pages.'.$page['key'].'.title')<small class="admin-field-error">{{ $message }}</small>@enderror
                        </label>
                        <label class="admin-seo-field">
                            <span>Meta description <small>Recommended: 140–160 characters</small></span>
                            <textarea data-seo-description name="pages[{{ $page['key'] }}][description]" rows="3" maxlength="320" required>{{ old('pages.'.$page['key'].'.description', $page['description']) }}</textarea>
                            @if(in_array($page['key'], ['category_detail', 'brand_detail'], true))<small>Use <code>{{ '{category}' }}</code> or <code>{{ '{brand}' }}</code> to personalize the template.</small>@endif
                            <small class="admin-seo-count" data-seo-description-count aria-live="polite"></small>
                            @error('pages.'.$page['key'].'.description')<small class="admin-field-error">{{ $message }}</small>@enderror
                        </label>

                        <details class="admin-seo-advanced">
                            <summary><span>Social sharing &amp; keywords</span><span aria-hidden="true">＋</span></summary>
                            <div class="admin-seo-advanced-fields">
                                <label class="admin-seo-field">Keywords <small>Optional</small><input name="pages[{{ $page['key'] }}][keywords]" value="{{ old('pages.'.$page['key'].'.keywords', $page['keywords']) }}" maxlength="500"></label>
                                <label class="admin-seo-field">Open Graph title <small>Shown when the page is shared</small><input name="pages[{{ $page['key'] }}][og_title]" value="{{ old('pages.'.$page['key'].'.og_title', $page['og_title']) }}" maxlength="255"></label>
                                <label class="admin-seo-field admin-seo-field-wide">Open Graph description <small>Optional; falls back to the meta description</small><textarea name="pages[{{ $page['key'] }}][og_description]" rows="2" maxlength="320">{{ old('pages.'.$page['key'].'.og_description', $page['og_description']) }}</textarea></label>
                            </div>
                        </details>
                    </div>

                    <aside class="admin-seo-search-preview" aria-label="Search result preview">
                        <div class="admin-seo-preview-label"><span aria-hidden="true">◉</span> SEARCH RESULT PREVIEW</div>
                        <div class="admin-seo-google-result">
                            <span class="admin-seo-preview-domain">crystalcaraudio.com <i>› {{ ltrim($page['path'], '/') }}</i></span>
                            <strong data-seo-preview-title>{{ old('pages.'.$page['key'].'.title', $page['title']) }}</strong>
                            <p data-seo-preview-description>{{ old('pages.'.$page['key'].'.description', $page['description']) }}</p>
                        </div>
                        <small>Search engines may choose different text for some searches.</small>
                    </aside>
                </div>
            </section>
        @endforeach

        <div class="admin-seo-savebar">
            <div><strong>Ready to publish your metadata?</strong><span>All page templates save together.</span></div>
            <button class="admin-button admin-button-primary" type="submit">Save SEO settings <span aria-hidden="true">→</span></button>
        </div>
    </form>
</div>
<script>
document.querySelectorAll('[data-seo-editor]').forEach((editor) => {
    const title = editor.querySelector('[data-seo-title]');
    const description = editor.querySelector('[data-seo-description]');
    const titlePreview = editor.querySelector('[data-seo-preview-title]');
    const descriptionPreview = editor.querySelector('[data-seo-preview-description]');
    const titleCount = editor.querySelector('[data-seo-title-count]');
    const descriptionCount = editor.querySelector('[data-seo-description-count]');
    const update = () => {
        titlePreview.textContent = title.value || 'Your page title will appear here';
        descriptionPreview.textContent = description.value || 'Your page description will appear here.';
        titleCount.textContent = `${title.value.length} / 60 characters`;
        descriptionCount.textContent = `${description.value.length} / 160 characters`;
        titleCount.classList.toggle('is-over', title.value.length > 60);
        descriptionCount.classList.toggle('is-over', description.value.length > 160);
    };
    title.addEventListener('input', update);
    description.addEventListener('input', update);
    update();
});
</script>
@endsection

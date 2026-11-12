@extends('admin.layout')
@section('title', ($editing ? 'Edit portfolio project' : 'Add portfolio project').' | Admin')
@section('admin_page_title', $editing ? 'Edit portfolio project' : 'Add portfolio project')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-catalogue admin-portfolio-page">
    <div class="admin-page-heading"><div><p class="eyebrow">INSTALLATION PORTFOLIO</p><h1>{{ $editing ? 'Edit project details' : 'Add a project' }}</h1><p>Follow the steps below. Add the vehicle and work, tag the products or categories, then choose the photos and publish the page.</p></div><a class="admin-secondary-action" href="{{ route('admin.portfolio') }}">← Back to projects</a></div>
    @if($errors->any())<div class="catalogue-alert" role="alert"><strong>Please review the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="catalogue-product-form standalone-product-form admin-portfolio-editor-form" method="post" action="{{ $editing ? route('admin.portfolio.update', $project['admin_key']) : route('admin.portfolio.store') }}" enctype="multipart/form-data">
        @csrf @if($editing)@method('PATCH')@endif
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">01</span><div><h2>Project and vehicle</h2><p>These details appear on the project page and in search results.</p></div></div>
            <div class="catalogue-form-grid">
                <label class="catalogue-field catalogue-field-wide">Project title<input required name="title" value="{{ old('title', $project['title']) }}" maxlength="190" data-portfolio-title placeholder="e.g. Toyota Axio full system upgrade"></label>
                <label class="catalogue-field catalogue-field-wide">Page URL<input required name="slug" value="{{ old('slug', $project['slug']) }}" maxlength="190" pattern="[a-z0-9]+(-[a-z0-9]+)*" data-portfolio-slug><small class="portfolio-url-preview">Public link: <strong>/our-portfolio/<span data-portfolio-slug-preview>{{ old('slug', $project['slug']) }}</span></strong>. It fills in from the title. Keep the current link when editing if you can, so existing Google results continue to work.</small></label>
                <label class="catalogue-field">Vehicle make<input required name="vehicle_make" value="{{ old('vehicle_make', $project['vehicle_make']) }}" maxlength="100" placeholder="Toyota"></label>
                <label class="catalogue-field">Vehicle model<input required name="vehicle_model" value="{{ old('vehicle_model', $project['vehicle_model']) }}" maxlength="160" placeholder="Axio"></label>
                <label class="catalogue-field">Vehicle year<input type="number" name="vehicle_year" value="{{ old('vehicle_year', $project['vehicle_year']) }}" min="1950" max="{{ now()->year + 1 }}"></label>
                <label class="catalogue-field">Work order <small>Optional</small><input name="work_order" value="{{ old('work_order', $project['work_order']) }}" maxlength="100" placeholder="e.g. CCA-2026-014"></label>
                <label class="catalogue-field catalogue-field-wide">Type of upgrade<input required name="upgrade_type" value="{{ old('upgrade_type', $project['upgrade_type']) }}" maxlength="190" placeholder="e.g. Android radio and reverse camera installation"></label>
                <label class="catalogue-field">Project date<input type="date" name="project_date" value="{{ old('project_date', $project['project_date']) }}"></label>
                <label class="catalogue-field">Service area<input name="service_area" value="{{ old('service_area', $project['service_area']) }}" maxlength="190" placeholder="e.g. Westlands, Nairobi"></label>
                <label class="catalogue-field">Client name <small>Optional</small><input name="client_name" value="{{ old('client_name', $project['client_name']) }}" maxlength="190"></label>
                <div class="catalogue-field catalogue-field-wide portfolio-description-field" data-markdown-editor>
                    <label for="portfolio-description">Project story and work completed</label>
                    <p class="portfolio-editor-help">Write what the customer wanted, what your team installed, and how the upgrade helps. The product and category links are added in the next step.</p>
                    <div class="portfolio-editor-toolbar" role="toolbar" aria-label="Project description formatting">
                        <button type="button" data-markdown-wrap="**" aria-label="Bold" title="Bold"><strong>B</strong></button>
                        <button type="button" data-markdown-wrap="*" aria-label="Italic" title="Italic"><em>I</em></button>
                        <button type="button" data-markdown-prefix="## " aria-label="Heading" title="Heading">H</button>
                        <span aria-hidden="true"></span>
                        <button type="button" data-markdown-prefix="- " aria-label="Bulleted list" title="Bulleted list">☷</button>
                        <button type="button" data-markdown-prefix="1. " aria-label="Numbered list" title="Numbered list">1.</button>
                        <button type="button" data-markdown-prefix="> " aria-label="Quote" title="Quote">“</button>
                    </div>
                    <textarea id="portfolio-description" name="description" rows="9" required maxlength="50000" data-markdown-input placeholder="Describe the customer’s needs, equipment fitted and work completed.">{{ old('description', $project['description']) }}</textarea>
                    <small>Use the buttons to format headings, emphasis and lists. A few clear paragraphs are enough. Avoid publishing personal contact details.</small>
                </div>
            </div>
        </section>
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">02</span><div><h2>Products and categories</h2><p>Connect this project to the items installed or the ranges customers can browse next.</p></div></div>
            <div class="portfolio-tagging-help"><span aria-hidden="true">↗</span><p><strong>Products open their product pages.</strong> Categories open the full range. If you cannot find an exact product, choose its category instead.</p></div>
            <div class="portfolio-product-picker" data-portfolio-product-picker data-product-search-url="{{ route('admin.portfolio.product-search') }}">
                <label class="catalogue-field catalogue-field-wide" for="portfolio-product-search">Find a product by name, brand or model</label>
                <input id="portfolio-product-search" class="portfolio-product-search" type="search" autocomplete="off" placeholder="Try “Sony”, “Android radio” or a model number" data-product-search>
                <div class="portfolio-product-search-results" data-product-search-results role="list"></div>
                <p class="portfolio-product-search-status" data-product-search-status role="status" aria-live="polite">Type at least 2 letters to search the product catalogue.</p>
                <div class="portfolio-selected-products" data-selected-products role="list" aria-label="Products linked to this project">
                    @foreach($selectedProducts as $selectedProduct)
                        <div class="portfolio-selected-product" role="listitem" data-selected-product data-product-slug="{{ $selectedProduct['slug'] }}">
                            @if($selectedProduct['image'])<img src="{{ $selectedProduct['image'] }}" alt="" loading="lazy">@endif
                            <span><strong>{{ $selectedProduct['name'] }}</strong><small>{{ $selectedProduct['brand'] ?: 'Brand not set' }}@if($selectedProduct['sku']) · {{ $selectedProduct['sku'] }}@endif</small></span>
                            <input type="hidden" name="related_product_slugs[]" value="{{ $selectedProduct['slug'] }}">
                            <button type="button" data-remove-product aria-label="Remove {{ $selectedProduct['name'] }}">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>
            <fieldset class="portfolio-category-picker">
                <legend>Link product categories <small>Optional</small></legend>
                <p>Choose categories that help customers continue browsing similar equipment.</p>
                <div class="portfolio-category-options">
                    @foreach($categories as $category)
                        <label><input type="checkbox" name="related_category_slugs[]" value="{{ $category->slug }}" @checked(in_array($category->slug, $selectedCategorySlugs, true))><span><strong>{{ $category->name }}</strong><small>Browse this category</small></span></label>
                    @endforeach
                </div>
            </fieldset>
        </section>
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">03</span><div><h2>Photos and video</h2><p>Keep the existing project photos, add more, or upload an installation video.</p></div></div>
            @if(!empty($project['image_paths']))
                <div class="admin-portfolio-existing-images">
                    @foreach($project['image_paths'] as $index => $imagePath)
                        <label><img src="{{ $project['images'][$index] ?? '' }}" alt="Existing project photo {{ $index + 1 }}" loading="lazy"><span><input type="checkbox" name="keep_images[]" value="{{ $imagePath }}" @checked(in_array($imagePath, old('keep_images', $project['image_paths']), true))> Keep photo {{ $index + 1 }}</span></label>
                    @endforeach
                </div>
            @endif
            <div class="catalogue-form-grid">
                <label class="catalogue-field catalogue-field-wide">Cover image
                    <select name="cover_image" data-portfolio-cover-image>
                        <option value="">Use the first selected photo</option>
                        @foreach($project['image_paths'] as $index => $imagePath)
                            <option value="{{ $imagePath }}" @selected(old('cover_image', $project['image_paths'][0] ?? '') === $imagePath)>Existing photo {{ $index + 1 }}</option>
                        @endforeach
                    </select>
                    <small>Choose which project photo appears first on the public portfolio page.</small>
                </label>
                <div class="catalogue-field catalogue-field-wide portfolio-photo-field">
                    <span>Add project photos</span>
                    <label class="portfolio-photo-dropzone" for="portfolio-images"><span aria-hidden="true">＋</span><strong>Choose photos to upload</strong><small>JPG, PNG or WebP · Up to 8 photos · 5 MB each</small><input id="portfolio-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-portfolio-images></label>
                    <div class="portfolio-photo-previews" data-portfolio-photo-previews aria-live="polite"></div>
                </div>
                <label class="catalogue-field catalogue-field-wide">Upload project video<input type="file" name="video" accept="video/mp4,video/webm"><small>MP4 or WebM, maximum 100 MB. Videos are shown with controls on the public project page.</small></label>
                @if($project['video_url'])<div class="catalogue-field catalogue-field-wide"><span>Current project video</span><video class="admin-portfolio-video-preview" src="{{ $project['video_url'] }}" controls preload="metadata"></video><label><input type="checkbox" name="remove_video" value="1"> Remove current video</label></div>@endif
            </div>
        </section>
        <section class="catalogue-form-section">
            <div class="product-form-section-heading"><span class="product-form-section-number">04</span><div><h2>Visibility</h2><p>Draft projects stay out of the public portfolio and sitemap.</p></div></div>
            <label class="portfolio-publish-toggle"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $project['is_published']))><span><strong>Publish this project</strong><small>Show it on the portfolio page and allow search engines to index its URL.</small></span></label>
        </section>
        <div class="catalogue-form-actions"><a class="admin-secondary-action" href="{{ route('admin.portfolio') }}">Cancel</a><button class="admin-primary-action" type="submit">{{ $editing ? 'Save project' : 'Create project' }} <span aria-hidden="true">→</span></button></div>
    </form>
</div>
<script>
(() => {
    const title = document.querySelector('[data-portfolio-title]');
    const slug = document.querySelector('[data-portfolio-slug]');
    if (title && slug) {
        const slugPreview = document.querySelector('[data-portfolio-slug-preview]');
        let slugEdited = Boolean(slug.value);
        const updateSlugPreview = () => { if (slugPreview) slugPreview.textContent = slug.value; };
        slug.addEventListener('input', () => { slugEdited = true; });
        title.addEventListener('input', () => {
            if (!slugEdited) slug.value = title.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            updateSlugPreview();
        });
        slug.addEventListener('input', updateSlugPreview);
    }

    const description = document.querySelector('[data-markdown-input]');
    document.querySelectorAll('[data-markdown-wrap], [data-markdown-prefix]').forEach(button => {
        button.addEventListener('click', () => {
            if (!description) return;
            const start = description.selectionStart;
            const end = description.selectionEnd;
            const value = description.value;
            const selected = value.slice(start, end) || 'text';
            const wrap = button.dataset.markdownWrap;
            const prefix = button.dataset.markdownPrefix;
            let replacement;
            let selectionStart;
            let selectionEnd;
            if (wrap) {
                replacement = `${wrap}${selected}${wrap}`;
                selectionStart = start + wrap.length;
                selectionEnd = selectionStart + selected.length;
            } else {
                replacement = selected.split('\n').map(line => `${prefix}${line}`).join('\n');
                selectionStart = start;
                selectionEnd = start + replacement.length;
            }
            description.setRangeText(replacement, start, end, 'select');
            description.focus();
            description.setSelectionRange(selectionStart, selectionEnd);
            description.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    const productPicker = document.querySelector('[data-portfolio-product-picker]');
    const productSearch = productPicker?.querySelector('[data-product-search]');
    const productResults = productPicker?.querySelector('[data-product-search-results]');
    const productSearchStatus = productPicker?.querySelector('[data-product-search-status]');
    const selectedProducts = productPicker?.querySelector('[data-selected-products]');
    let productSearchTimer;
    let productSearchController;

    const addSelectedProduct = product => {
        if (!selectedProducts || [...selectedProducts.querySelectorAll('[data-selected-product]')].some(item => item.dataset.productSlug === product.slug)) return;
        const selected = document.createElement('div');
        selected.className = 'portfolio-selected-product';
        selected.setAttribute('role', 'listitem');
        selected.dataset.selectedProduct = '';
        selected.dataset.productSlug = product.slug;

        if (product.image) {
            const image = document.createElement('img');
            image.src = product.image;
            image.alt = '';
            image.loading = 'lazy';
            selected.append(image);
        }

        const copy = document.createElement('span');
        const name = document.createElement('strong');
        const detail = document.createElement('small');
        name.textContent = product.name;
        detail.textContent = [product.brand || 'Brand not set', product.sku].filter(Boolean).join(' · ');
        copy.append(name, detail);

        const value = document.createElement('input');
        value.type = 'hidden';
        value.name = 'related_product_slugs[]';
        value.value = product.slug;

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.dataset.removeProduct = '';
        remove.setAttribute('aria-label', `Remove ${product.name}`);
        remove.textContent = 'Remove';
        selected.append(copy, value, remove);
        selectedProducts.append(selected);
        renderProductResults([]);
        productSearchStatus.textContent = `${product.name} added. Search for another product or save the project.`;
        productSearch.value = '';
        productSearch.focus();
    };

    const renderProductResults = products => {
        if (!productResults) return;
        productResults.replaceChildren();
        products.filter(product => ![...selectedProducts.querySelectorAll('[data-selected-product]')].some(item => item.dataset.productSlug === product.slug)).forEach(product => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'portfolio-product-search-result';
            option.setAttribute('role', 'listitem');
            option.setAttribute('aria-label', `Add ${product.name}`);

            if (product.image) {
                const image = document.createElement('img');
                image.src = product.image;
                image.alt = '';
                image.loading = 'lazy';
                option.append(image);
            }

            const copy = document.createElement('span');
            const name = document.createElement('strong');
            const detail = document.createElement('small');
            name.textContent = product.name;
            detail.textContent = [product.brand, product.sku, product.category].filter(Boolean).join(' · ');
            copy.append(name, detail);
            const action = document.createElement('b');
            action.setAttribute('aria-hidden', 'true');
            action.textContent = '+';
            option.append(copy, action);
            option.addEventListener('click', () => addSelectedProduct(product));
            productResults.append(option);
        });
    };

    productSearch?.addEventListener('input', () => {
        window.clearTimeout(productSearchTimer);
        productSearchController?.abort();
        const query = productSearch.value.trim();
        productResults.replaceChildren();
        if (query.length < 2) {
            productSearchStatus.textContent = 'Type at least 2 letters to search the product catalogue.';
            return;
        }

        productSearchStatus.textContent = 'Searching products…';
        productSearchTimer = window.setTimeout(async () => {
            productSearchController = new AbortController();
            const url = new URL(productPicker.dataset.productSearchUrl, window.location.href);
            url.searchParams.set('q', query);
            try {
                const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: productSearchController.signal });
                if (!response.ok) throw new Error('Search is temporarily unavailable. Try again.');
                const result = await response.json();
                const products = result.products || [];
                renderProductResults(products);
                productSearchStatus.textContent = products.length ? `${products.length} product${products.length === 1 ? '' : 's'} found. Select one to add it.` : 'No matching products found. Try a different name or model.';
            } catch (error) {
                if (error.name !== 'AbortError') productSearchStatus.textContent = error.message || 'Could not search products. Please try again.';
            }
        }, 250);
    });

    selectedProducts?.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-product]');
        if (!remove) return;
        remove.closest('[data-selected-product]').remove();
        productSearch.focus();
    });

    const photoInput = document.querySelector('[data-portfolio-images]');
    const coverSelect = document.querySelector('[data-portfolio-cover-image]');
    const previewGrid = document.querySelector('[data-portfolio-photo-previews]');
    const photoDropzone = document.querySelector('.portfolio-photo-dropzone');
    let previewUrls = [];
    const renderPhotoPreviews = () => {
        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];
        previewGrid.replaceChildren();
        [...photoInput.files].forEach((file, index) => {
            const value = `upload:${index}`;
            let option = [...coverSelect.options].find(item => item.value === value);
            if (!option) {
                option = new Option(file.name, value);
                coverSelect.add(option);
            }
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            const preview = document.createElement('figure');
            const image = document.createElement('img');
            image.src = url;
            image.alt = `Selected project photo ${index + 1}`;
            const caption = document.createElement('figcaption');
            caption.textContent = file.name;
            preview.append(image, caption);
            previewGrid.append(preview);
        });
        if (photoInput.files.length && !coverSelect.value) coverSelect.value = 'upload:0';
    };
    photoInput?.addEventListener('change', renderPhotoPreviews);
    photoDropzone?.addEventListener('dragover', event => {
        event.preventDefault();
        photoDropzone.classList.add('is-dragging');
    });
    photoDropzone?.addEventListener('dragleave', event => {
        if (!photoDropzone.contains(event.relatedTarget)) photoDropzone.classList.remove('is-dragging');
    });
    photoDropzone?.addEventListener('drop', event => {
        event.preventDefault();
        photoDropzone.classList.remove('is-dragging');
        const transfer = new DataTransfer();
        [...event.dataTransfer.files].filter(file => ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)).slice(0, 8).forEach(file => transfer.items.add(file));
        photoInput.files = transfer.files;
        renderPhotoPreviews();
    });
})();
</script>
<style>
    .admin-portfolio-editor-form{gap:20px}
    .admin-portfolio-editor-form .catalogue-form-section{padding:clamp(18px,3vw,30px);border:1px solid #e4ebe7;border-radius:14px;background:linear-gradient(160deg,#fff,#fbfdfc);box-shadow:0 8px 26px rgba(29,59,43,.045)}
    .admin-portfolio-editor-form .product-form-section-heading{margin-bottom:22px}
    .admin-portfolio-editor-form .catalogue-field>label,.admin-portfolio-editor-form .catalogue-field>span{display:block;margin-bottom:8px;color:#33463c;font-size:13px;font-weight:600}
    .portfolio-url-preview{display:block;margin-top:8px;color:#69786f;font-size:12px;line-height:1.5}
    .portfolio-url-preview strong{color:#0c6661;overflow-wrap:anywhere}
    .admin-portfolio-editor-form .portfolio-description-field>label{display:block;margin-bottom:8px;color:#33463c;font-size:13px;font-weight:600}
    .portfolio-editor-help{margin:0 0 11px;color:#68776e;font-size:13px;line-height:1.6}
    .portfolio-editor-toolbar{display:flex;align-items:center;gap:5px;padding:7px;border:1px solid #dce6e0;border-bottom:0;border-radius:8px 8px 0 0;background:#f5f8f6}
    .portfolio-editor-toolbar button{min-width:34px;height:32px;padding:0 8px;border:1px solid transparent;border-radius:5px;background:transparent;color:#34473d;cursor:pointer;font:600 13px/1 'Outfit',sans-serif}
    .portfolio-editor-toolbar button:hover,.portfolio-editor-toolbar button:focus-visible{border-color:#cbdcd2;background:#fff;outline:none}
    .portfolio-editor-toolbar>span{width:1px;height:20px;margin:0 4px;background:#d6e0da}
    .portfolio-description-field textarea{border-radius:0 0 8px 8px!important;font:400 14px/1.7 'Outfit','Cairo',sans-serif!important}
    .portfolio-photo-dropzone{position:relative;display:grid;justify-items:center;gap:8px;padding:25px 18px;border:1.5px dashed #afc8ba;border-radius:10px;background:#f7faf8;text-align:center;cursor:pointer;transition:border-color .18s,background .18s}
    .portfolio-photo-dropzone:hover,.portfolio-photo-dropzone.is-dragging{border-color:#0c6661;background:#f0f7f3}
    .portfolio-photo-dropzone>span{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:#e3f1e9;color:#0c6661;font-size:22px}
    .portfolio-photo-dropzone strong{color:#2f4438;font-size:13px}
    .portfolio-photo-dropzone small{color:#74837a;font-size:12px}
    .portfolio-photo-dropzone input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
    .portfolio-photo-previews{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-top:12px}
    .portfolio-photo-previews:empty{display:none}
    .portfolio-photo-previews figure{min-width:0;margin:0;overflow:hidden;border:1px solid #e1e9e4;border-radius:8px;background:#fff}
    .portfolio-photo-previews img{display:block;width:100%;height:100px;object-fit:cover}
    .portfolio-photo-previews figcaption{overflow:hidden;padding:7px 8px;color:#64746a;font-size:11px;text-overflow:ellipsis;white-space:nowrap}
    .portfolio-tagging-help{display:flex;align-items:flex-start;gap:12px;margin:0 0 19px;padding:13px 15px;border:1px solid #d8e9df;border-radius:9px;background:#f2f8f4;color:#52655a}
    .portfolio-tagging-help>span{display:grid;flex:0 0 28px;height:28px;place-items:center;border-radius:50%;background:#dcefe3;color:#08746e;font-weight:700}
    .portfolio-tagging-help p{margin:0;font-size:13px;line-height:1.55}
    .portfolio-tagging-help strong{color:#31463a}
    .portfolio-product-picker{position:relative}
    .portfolio-product-picker>.catalogue-field{display:block;margin-bottom:8px;color:#33463c;font-size:13px;font-weight:600}
    .portfolio-product-search{display:block;width:100%;min-height:48px;padding:12px 14px;border:1px solid #dce6e0;border-radius:9px;background:#fff;color:#25312b;font:400 14px/20px 'Outfit','Cairo',sans-serif}
    .portfolio-product-search:focus{outline:3px solid #0c716b22;border-color:#0c8a80}
    .portfolio-product-search-results{display:grid;gap:5px;margin-top:7px}
    .portfolio-product-search-result{display:grid;grid-template-columns:42px minmax(0,1fr) 24px;align-items:center;gap:11px;width:100%;padding:8px 10px;border:1px solid #e1e9e4;border-radius:8px;background:#fff;text-align:left;cursor:pointer}
    .portfolio-product-search-result:hover,.portfolio-product-search-result:focus-visible{border-color:#7eb7a1;background:#f6faf7;outline:none}
    .portfolio-product-search-result>img,.portfolio-selected-product>img{width:42px;height:42px;object-fit:contain;border-radius:6px;background:#f3f6f4}
    .portfolio-product-search-result>span,.portfolio-selected-product>span{display:grid;min-width:0;gap:3px}
    .portfolio-product-search-result strong,.portfolio-selected-product strong{overflow:hidden;color:#2c3c33;font-size:13px;text-overflow:ellipsis;white-space:nowrap}
    .portfolio-product-search-result small,.portfolio-selected-product small{overflow:hidden;color:#748178;font-size:11px;text-overflow:ellipsis;white-space:nowrap}
    .portfolio-product-search-result>b{display:grid;width:24px;height:24px;place-items:center;border-radius:50%;background:#e2f2e8;color:#08746e;font-size:18px}
    .portfolio-product-search-status{min-height:18px;margin:7px 0;color:#718077;font-size:12px}
    .portfolio-selected-products{display:grid;gap:7px;margin-top:11px}
    .portfolio-selected-product{display:grid;grid-template-columns:42px minmax(0,1fr) auto;align-items:center;gap:11px;padding:8px;border:1px solid #dce8e0;border-radius:8px;background:#f7faf8}
    .portfolio-selected-product>button{padding:7px 9px;border:1px solid #e8d9d6;border-radius:6px;background:#fff;color:#8f433b;font-size:11px;cursor:pointer}
    .portfolio-category-picker{margin:22px 0 0;padding:0;border:0}
    .portfolio-category-picker legend{margin-bottom:5px;color:#33463c;font-size:13px;font-weight:700}
    .portfolio-category-picker legend small{margin-left:5px;color:#7b8981;font-weight:400}
    .portfolio-category-picker>p{margin:0 0 12px;color:#718077;font-size:12px;line-height:1.5}
    .portfolio-category-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}
    .portfolio-category-options>label{display:flex;align-items:flex-start;gap:9px;min-width:0;padding:12px;border:1px solid #e1e9e4;border-radius:8px;background:#fff;cursor:pointer}
    .portfolio-category-options>label:has(input:checked){border-color:#7bb99d;background:#f2f8f4}
    .portfolio-category-options input{flex:0 0 auto;margin:3px 0 0;accent-color:#08746e}
    .portfolio-category-options>label>span{display:grid;gap:3px;min-width:0}
    .portfolio-category-options strong{color:#34463b;font-size:12px;line-height:1.35}
    .portfolio-category-options small{color:#7a877f;font-size:10px;line-height:1.35}
    @media(max-width:900px){.portfolio-category-options{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.admin-portfolio-editor-form .catalogue-form-section{padding:17px}.portfolio-photo-previews{grid-template-columns:repeat(2,minmax(0,1fr))}.portfolio-category-options{grid-template-columns:1fr}.portfolio-selected-product{grid-template-columns:36px minmax(0,1fr) auto;gap:8px}.portfolio-selected-product>img{width:36px;height:36px}}
</style>
@endsection

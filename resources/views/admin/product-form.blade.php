@extends('admin.layout')
@section('title', ($editingProduct ? 'Edit product' : 'Add product').' | Admin')
@section('admin_page_title', $editingProduct ? 'Edit product' : 'Add product')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-catalogue admin-product-editor-page">
    <div class="admin-page-heading"><div><p class="eyebrow">PRODUCT CATALOGUE</p><h1>{{ $editingProduct ? 'Edit product' : 'Add a product' }}</h1><p>{{ $editingProduct ? 'Update product details and storefront visibility.' : 'Add a product to your catalogue with pricing, stock, and product details.' }}</p></div><a class="admin-secondary-action" href="{{ route('admin.products') }}">← Back to products</a></div>
    <div class="catalogue-alert product-form-feedback" role="alert" hidden></div>
    @if($errors->any())<div class="catalogue-alert" role="alert"><strong>Please review the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="catalogue-product-form standalone-product-form" data-product-form method="post" action="{{ $editingProduct ? route('admin.products.update', $editingProduct) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf @if($editingProduct)@method('PATCH')@endif
        <input type="hidden" name="_meta_title_generated" data-meta-title-generated value="{{ $metaTitleGenerated ? '1' : '0' }}">
        <input type="hidden" name="_meta_description_generated" data-meta-description-generated value="{{ $metaDescriptionGenerated ? '1' : '0' }}">
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">01</span><div><h2>Product details</h2><p>Name, category, brand, and customer-facing description.</p></div></div>
            <div class="catalogue-form-grid">
                <label class="catalogue-field catalogue-field-wide">Product name<input required name="name" value="{{ old('name', $editingProduct->name ?? '') }}" placeholder="e.g. 10-inch Android Mirror DVR"></label>
                <label class="catalogue-field">URL slug<input name="slug" value="{{ old('slug', $editingProduct->slug ?? '') }}" placeholder="Generated from the product name if blank"></label>
                <label class="catalogue-field">SKU<input name="sku" value="{{ old('sku', $editingProduct->sku ?? '') }}" placeholder="Optional stock code"></label>
                <label class="catalogue-field">Product category<select name="category"><option value="">Select a category</option>@foreach($categories as $option)<option value="{{ $option }}" @selected(old('category', $editingProduct->category ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
                <label class="catalogue-field">Brand<select name="brand"><option value="">Select a brand</option>@foreach($brands as $option)<option value="{{ $option }}" @selected(old('brand', $editingProduct->brand ?? '') === $option)>{{ $option }}</option>@endforeach</select><small>Manage brand options from existing catalogue products.</small></label>
                <div class="catalogue-field catalogue-field-wide"><span>Description</span><div class="product-rich-editor" data-rich-editor>
                    <div class="product-rich-toolbar" role="toolbar" aria-label="Description formatting">
                        <button type="button" data-editor-command="bold" aria-label="Bold"><strong>B</strong></button><button type="button" data-editor-command="italic" aria-label="Italic"><em>I</em></button><span aria-hidden="true"></span><button type="button" data-editor-command="insertUnorderedList" aria-label="Bulleted list">• List</button><button type="button" data-editor-command="insertOrderedList" aria-label="Numbered list">1. List</button>
                    </div>
                    <div class="product-rich-content" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Product description" data-editor-content>{!! $descriptionHtml !!}</div>
                    <textarea class="product-rich-value" name="description" data-editor-value hidden>{{ old('description', $editingProduct->description ?? '') }}</textarea>
                </div><small>Describe fitment, key features, and what is included.</small></div>
            </div>
        </section>
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">02</span><div><h2>Price and availability</h2><p>Set the current selling price and the previous price shown for comparison.</p></div></div>
            <div class="catalogue-form-grid catalogue-form-grid-four">
                <label class="catalogue-field">Real price (KES)<input required type="number" min="0" step="0.01" name="price" value="{{ old('price', $editingProduct->price ?? '0') }}"></label>
                <label class="catalogue-field">Previous price (KES)<input type="number" min="0" step="0.01" name="original_price" value="{{ old('original_price', $editingProduct->original_price ?? '') }}"></label>
                <label class="catalogue-field">Cost price (KES)<input type="number" min="0" step="0.01" name="cost_price" value="{{ old('cost_price', $editingProduct->cost_price ?? '') }}"></label>
                <label class="catalogue-field">Stock status<select name="stock"><option value="In Stock" @selected(old('stock', $editingProduct->stock ?? 'In Stock') === 'In Stock')>In stock</option><option value="Out of Stock" @selected(old('stock', $editingProduct->stock ?? '') === 'Out of Stock')>Out of stock</option><option value="Pre-order" @selected(old('stock', $editingProduct->stock ?? '') === 'Pre-order')>Pre-order</option></select></label>
                <label class="catalogue-field">Quantity<input type="number" min="0" step="1" name="stock_quantity" value="{{ old('stock_quantity', $editingProduct->stock_quantity ?? '') }}" placeholder="Leave blank to not track"></label>
                <label class="catalogue-field">Low-stock alert at<input type="number" min="0" step="1" name="low_stock_threshold" value="{{ old('low_stock_threshold', $editingProduct->low_stock_threshold ?? 5) }}"></label>
            </div>
        </section>
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">03</span><div><h2>Product images</h2><p>Upload up to 10 images. PNG, JPG, and WebP up to 5 MB each.</p></div></div>
            <label class="product-image-dropzone" data-dropzone><input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" data-image-input multiple><span class="product-image-placeholder" data-image-placeholder><b aria-hidden="true">↑</b><strong>Drop images here, or browse files</strong><small>Select multiple product views. Choose which image should appear first.</small></span></label>
            <fieldset class="product-primary-image-choices product-upload-main-choice" data-upload-main-choice hidden><legend>Main image <small>Select the uploaded image shown first on the product page.</small></legend><div class="product-primary-image-grid" data-upload-image-grid></div><p class="product-primary-image-note">Choose one image as the main product image. The remaining images will be saved in the gallery.</p></fieldset>
            @php
                $imageChoices = collect($editingProduct?->gallery ?? [])->filter()->push($editingProduct?->image)->filter()->unique()->values();
                $primaryImageChoice = old('primary_image', $imageChoices->first());
            @endphp
            @if($imageChoices->isNotEmpty())
                <fieldset class="product-primary-image-choices"><legend>Main image <small>Select the image shown first on the product page.</small></legend><div class="product-primary-image-grid">
                    @foreach($imageChoices as $imageChoice)
                        <label class="product-primary-image-choice"><input type="radio" name="primary_image" value="{{ $imageChoice }}" @checked($primaryImageChoice === $imageChoice)><span class="product-primary-image-preview"><img loading="lazy" decoding="async" src="{{ str_starts_with($imageChoice, 'http') ? $imageChoice : \App\Support\MediaUrl::url('uploads/'.ltrim($imageChoice, '/')) }}" alt=""></span><span class="product-primary-image-copy"><strong>Image {{ $loop->iteration }}</strong><small>{{ basename($imageChoice) }}</small></span><span class="product-primary-image-check" aria-hidden="true">✓</span></label>
                    @endforeach
                </div><p class="product-primary-image-note" data-primary-image-note>A newly uploaded image becomes the main image automatically. Select an existing image here to make it the main image instead.</p></fieldset>
            @else
                <p class="product-primary-image-note" data-primary-image-note>A newly uploaded image will be used as the main product image.</p>
            @endif
        </section>
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">04</span><div><h2>Store visibility and SEO</h2><p>Products appear in the store when saved. Promotion badges are optional; SEO fields are suggested from your product details and remain editable.</p></div></div>
            <div class="catalogue-flag-row">@foreach(['featured' => 'Featured pick', 'is_offer' => 'Offer', 'is_special' => 'Special', 'trending' => 'Trending'] as $flag => $label)<label class="catalogue-check"><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $editingProduct?->{$flag} ?? false))><span>{{ $label }}</span></label>@endforeach</div>
            <div class="catalogue-form-grid"><label class="catalogue-field">SEO title<input data-seo-title data-auto-generated="{{ $metaTitleGenerated ? 'true' : 'false' }}" name="meta_title" maxlength="255" value="{{ old('meta_title', $metaTitle) }}" placeholder="Generated from product name, brand, and category"><small data-seo-title-hint>{{ $metaTitleGenerated ? 'Suggested from product details. Edit to customize.' : 'Custom SEO title.' }}</small></label><label class="catalogue-field">SEO description<textarea data-seo-description data-auto-generated="{{ $metaDescriptionGenerated ? 'true' : 'false' }}" name="meta_description" maxlength="320" rows="3" placeholder="Generated from the product description">{{ old('meta_description', $metaDescription) }}</textarea><small data-seo-description-hint>{{ $metaDescriptionGenerated ? 'Suggested from the product description. Edit to customize.' : 'Custom SEO description.' }}</small></label></div>
        </section>
        <div class="catalogue-form-actions"><a class="catalogue-reset" href="{{ route('admin.products') }}">Cancel</a><button class="admin-primary-action" type="submit" data-submit-button>{{ $editingProduct ? 'Save changes' : 'Add product' }} <span aria-hidden="true">→</span></button></div>
    </form>
</div>
<script>
(() => {
    const form = document.querySelector('[data-product-form]');
    if (!form) return;
    const editor = form.querySelector('[data-editor-content]');
    const editorValue = form.querySelector('[data-editor-value]');
    const seoTitleInput = form.querySelector('[data-seo-title]');
    const seoDescriptionInput = form.querySelector('[data-seo-description]');
    const nameInput = form.querySelector('[name="name"]');
    const brandInput = form.querySelector('[name="brand"]');
    const categoryInput = form.querySelector('[name="category"]');
    const seoTitleHint = form.querySelector('[data-seo-title-hint]');
    const seoDescriptionHint = form.querySelector('[data-seo-description-hint]');
    const seoTitleGeneratedFlag = form.querySelector('[data-meta-title-generated]');
    const seoDescriptionGeneratedFlag = form.querySelector('[data-meta-description-generated]');
    const dropzone = form.querySelector('[data-dropzone]');
    const imageInput = form.querySelector('[data-image-input]');
    const placeholder = form.querySelector('[data-image-placeholder]');
    const uploadMainChoice = form.querySelector('[data-upload-main-choice]');
    const uploadImageGrid = form.querySelector('[data-upload-image-grid]');
    const feedback = document.querySelector('.product-form-feedback');
    const submitButton = form.querySelector('[data-submit-button]');
    const primaryChoices = [...form.querySelectorAll('input[name="primary_image"]')];
    let selectedFiles = [];
    let selectedUploadIndex = null;
    let primaryChoiceBeforeUpload = primaryChoices.find(choice => choice.checked)?.value || null;
    const imagePreviewUrls = new Map();
    let seoTitleCustomized = seoTitleInput.dataset.autoGenerated !== 'true';
    let seoDescriptionCustomized = seoDescriptionInput.dataset.autoGenerated !== 'true';
    const truncateAtWord = (value, limit) => {
        const text = value.trim().replace(/\s+/g, ' ');
        if (text.length <= limit) return text;
        return `${text.slice(0, limit - 1).replace(/\s+\S*$/, '').trim()}…`;
    };
    const updateSeoSuggestions = () => {
        const productName = nameInput.value.trim();
        if (!productName) return;
        const brand = brandInput.value.trim();
        const category = categoryInput.value.trim();
        const qualifier = [brand, category].filter(Boolean).join(' ');
        const seoTitle = truncateAtWord(`${productName}${qualifier ? ` | ${qualifier}` : ''} | Crystal Car Audio`, 70);
        const descriptionText = (editor.innerText || editor.textContent || '').replace(/\s+/g, ' ').trim();
        const seoDescription = truncateAtWord(descriptionText || `Shop ${[brand, productName, category].filter(Boolean).join(' ')} at Crystal Car Audio Kenya. Get expert advice and delivery across Kenya.`, 155);
        if (!seoTitleCustomized) {
            seoTitleInput.value = seoTitle;
            seoTitleGeneratedFlag.value = '1';
            seoTitleHint.textContent = 'Suggested from product name, brand, and category. Edit to customize.';
        }
        if (!seoDescriptionCustomized) {
            seoDescriptionInput.value = seoDescription;
            seoDescriptionGeneratedFlag.value = '1';
            seoDescriptionHint.textContent = descriptionText ? 'Suggested from the product description. Edit to customize.' : 'Suggested from product details. Edit to customize.';
        }
    };
    seoTitleInput.addEventListener('input', () => {
        seoTitleCustomized = seoTitleInput.value.trim() !== '';
        seoTitleInput.dataset.autoGenerated = seoTitleCustomized ? 'false' : 'true';
        seoTitleGeneratedFlag.value = seoTitleCustomized ? '0' : '1';
        if (seoTitleCustomized) seoTitleHint.textContent = 'Custom SEO title. Product detail edits will not replace it.';
        updateSeoSuggestions();
    });
    seoDescriptionInput.addEventListener('input', () => {
        seoDescriptionCustomized = seoDescriptionInput.value.trim() !== '';
        seoDescriptionInput.dataset.autoGenerated = seoDescriptionCustomized ? 'false' : 'true';
        seoDescriptionGeneratedFlag.value = seoDescriptionCustomized ? '0' : '1';
        if (seoDescriptionCustomized) seoDescriptionHint.textContent = 'Custom SEO description. Product detail edits will not replace it.';
        updateSeoSuggestions();
    });
    nameInput.addEventListener('input', updateSeoSuggestions);
    brandInput.addEventListener('change', updateSeoSuggestions);
    categoryInput.addEventListener('change', updateSeoSuggestions);
    const syncDescription = () => { editorValue.value = editor.innerHTML.trim(); updateSeoSuggestions(); };
    editor.addEventListener('input', syncDescription);
    updateSeoSuggestions();
    form.querySelectorAll('[data-editor-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => { editor.focus(); document.execCommand(button.dataset.editorCommand, false); syncDescription(); });
    });
    const syncUploadedImages = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach(file => transfer.items.add(file));
        imageInput.files = transfer.files;
        imagePreviewUrls.forEach(url => URL.revokeObjectURL(url));
        imagePreviewUrls.clear();
        uploadImageGrid.replaceChildren();
        uploadMainChoice.hidden = selectedFiles.length === 0;
        placeholder.hidden = selectedFiles.length > 0;
        selectedFiles.forEach((file, index) => {
            const url = URL.createObjectURL(file);
            imagePreviewUrls.set(index, url);
            const wrapper = document.createElement('div');
            wrapper.className = 'product-upload-choice-wrap';
            const label = document.createElement('label');
            label.className = 'product-primary-image-choice';
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'primary_upload_index';
            radio.value = String(index);
            radio.checked = selectedUploadIndex === index;
            radio.addEventListener('change', () => {
                selectedUploadIndex = index;
                primaryChoices.forEach(choice => { choice.checked = false; });
            });
            const imageFrame = document.createElement('span');
            imageFrame.className = 'product-primary-image-preview';
            const image = document.createElement('img');
            image.src = url;
            image.alt = '';
            imageFrame.append(image);
            const copy = document.createElement('span');
            copy.className = 'product-primary-image-copy';
            const title = document.createElement('strong');
            title.textContent = 'Upload ' + (index + 1);
            const filename = document.createElement('small');
            filename.textContent = file.name;
            copy.append(title, filename);
            const check = document.createElement('span');
            check.className = 'product-primary-image-check';
            check.setAttribute('aria-hidden', 'true');
            check.textContent = '✓';
            label.append(radio, imageFrame, copy, check);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'product-upload-remove';
            remove.setAttribute('aria-label', 'Remove ' + file.name);
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                if (selectedUploadIndex === index) selectedUploadIndex = selectedFiles.length ? Math.min(index, selectedFiles.length - 1) : null;
                else if (selectedUploadIndex !== null && selectedUploadIndex > index) selectedUploadIndex -= 1;
                syncUploadedImages();
                if (selectedFiles.length === 0 && primaryChoiceBeforeUpload) {
                    const previousChoice = primaryChoices.find(choice => choice.value === primaryChoiceBeforeUpload);
                    if (previousChoice) previousChoice.checked = true;
                    selectedUploadIndex = null;
                }
            });
            wrapper.append(label, remove);
            uploadImageGrid.append(wrapper);
        });
    };
    const addFiles = files => {
        const incomingFiles = [...files].filter(file => file.type.startsWith('image/'));
        if (!incomingFiles.length) return;
        if (selectedFiles.length === 0) {
            primaryChoiceBeforeUpload = primaryChoices.find(choice => choice.checked)?.value || null;
            primaryChoices.forEach(choice => { choice.checked = false; });
            selectedUploadIndex = 0;
        }
        selectedFiles = [...selectedFiles, ...incomingFiles].slice(0, 10);
        syncUploadedImages();
    };
    imageInput.addEventListener('change', () => addFiles(imageInput.files));
    primaryChoices.forEach(choice => choice.addEventListener('change', () => {
        if (choice.checked) {
            primaryChoiceBeforeUpload = choice.value;
            selectedUploadIndex = null;
            uploadImageGrid.querySelectorAll('input[name="primary_upload_index"]').forEach(radio => { radio.checked = false; });
        }
    }));
    dropzone.addEventListener('dragover', event => { event.preventDefault(); dropzone.classList.add('is-dragging'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragging'));
    dropzone.addEventListener('drop', event => { event.preventDefault(); dropzone.classList.remove('is-dragging'); addFiles(event.dataTransfer.files); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        syncDescription();
        feedback.hidden = true;
        feedback.replaceChildren();
        submitButton.disabled = true;
        submitButton.dataset.originalText = submitButton.textContent.trim();
        submitButton.textContent = 'Saving…';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            const result = await response.json();
            if (!response.ok) {
                const errors = Object.values(result.errors || {}).flat();
                feedback.innerHTML = `<strong>We couldn’t save this product yet.</strong><ul>${errors.map(message => `<li>${message.replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]))}</li>`).join('')}</ul>`;
                feedback.hidden = false;
                feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            window.location.assign(result.redirect || '{{ route('admin.products') }}');
        } catch (error) {
            feedback.textContent = 'The save request could not be completed. Your entries are still here; please try again.';
            feedback.hidden = false;
        } finally {
            submitButton.disabled = false;
            if (!feedback.hidden || !submitButton.disabled) submitButton.textContent = submitButton.dataset.originalText;
        }
    });
})();
</script>
@endsection

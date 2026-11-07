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
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">03</span><div><h2>Product image</h2><p>Upload a clear product image. PNG, JPG, and WebP up to 5 MB.</p></div></div>
            <label class="product-image-dropzone" data-dropzone><input type="file" name="image" accept="image/png,image/jpeg,image/webp" data-image-input><span class="product-image-placeholder" data-image-placeholder><b aria-hidden="true">↑</b><strong>Drop an image here, or browse files</strong><small>Use a clean product image with the item centered in frame.</small></span><span class="product-image-preview" data-image-preview @if(!$editingProduct?->image) hidden @endif>@if($editingProduct?->image)<img src="{{ str_starts_with($editingProduct->image, 'http') ? $editingProduct->image : \App\Support\MediaUrl::url('uploads/'.ltrim($editingProduct->image, '/')) }}" alt="Current product image">@endif<span data-image-name>{{ $editingProduct?->image ? basename($editingProduct->image) : '' }}</span><button type="button" data-remove-image aria-label="Remove selected image" hidden>×</button></span></label>
        </section>
        <section class="catalogue-form-section"><div class="product-form-section-heading"><span class="product-form-section-number">04</span><div><h2>Store visibility and SEO</h2><p>Choose product badges and a search result title and description.</p></div></div>
            <div class="catalogue-flag-row">@foreach(['featured' => 'Featured pick', 'is_offer' => 'Offer', 'is_special' => 'Special', 'trending' => 'Trending'] as $flag => $label)<label class="catalogue-check"><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $editingProduct?->{$flag} ?? false))><span>{{ $label }}</span></label>@endforeach</div>
            <div class="catalogue-form-grid"><label class="catalogue-field">SEO title<input name="meta_title" maxlength="255" value="{{ old('meta_title', $editingProduct->meta_title ?? '') }}" placeholder="Search result title"></label><label class="catalogue-field">SEO description<textarea name="meta_description" maxlength="320" rows="3" placeholder="Short search result description">{{ old('meta_description', $editingProduct->meta_description ?? '') }}</textarea></label></div>
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
    const dropzone = form.querySelector('[data-dropzone]');
    const imageInput = form.querySelector('[data-image-input]');
    const placeholder = form.querySelector('[data-image-placeholder]');
    const preview = form.querySelector('[data-image-preview]');
    const feedback = document.querySelector('.product-form-feedback');
    const submitButton = form.querySelector('[data-submit-button]');
    const existingPreview = preview.innerHTML;
    const hasExistingPreview = !preview.hidden;
    let previewUrl;
    const syncDescription = () => { editorValue.value = editor.innerHTML.trim(); };
    editor.addEventListener('input', syncDescription);
    form.querySelectorAll('[data-editor-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => { editor.focus(); document.execCommand(button.dataset.editorCommand, false); syncDescription(); });
    });
    const showFile = file => {
        if (!file || !file.type.startsWith('image/')) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.innerHTML = `<img src="${previewUrl}" alt="Product image preview"><span data-image-name></span><button type="button" data-remove-image aria-label="Remove selected image">×</button>`;
        preview.querySelector('[data-image-name]').textContent = file.name;
        placeholder.hidden = true;
        preview.hidden = false;
        const removeButton = preview.querySelector('[data-remove-image]');
        removeButton.hidden = false;
        removeButton.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); clearFile(); });
    };
    const clearFile = () => { imageInput.value = ''; if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null; preview.innerHTML = hasExistingPreview ? existingPreview : ''; preview.hidden = !hasExistingPreview; placeholder.hidden = hasExistingPreview; preview.querySelector('[data-remove-image]')?.addEventListener('click', clearFile); };
    imageInput.addEventListener('change', () => showFile(imageInput.files[0]));
    dropzone.addEventListener('dragover', event => { event.preventDefault(); dropzone.classList.add('is-dragging'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('is-dragging'));
    dropzone.addEventListener('drop', event => { event.preventDefault(); dropzone.classList.remove('is-dragging'); const file = event.dataTransfer.files[0]; if (file) { const transfer = new DataTransfer(); transfer.items.add(file); imageInput.files = transfer.files; showFile(file); } });
    preview.querySelector('[data-remove-image]')?.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); clearFile(); });
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

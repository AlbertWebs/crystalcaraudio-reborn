@extends('admin.layout')
@section('title', 'Categories | Admin')
@section('admin_page_title', 'Categories')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-directory-page">
    <div class="admin-page-heading"><div><p class="eyebrow">STORE MANAGEMENT</p><h1>Categories</h1><p>Set how catalogue categories are ordered across the storefront.</p></div><div class="admin-heading-actions"><button class="admin-primary-action" type="button" @click="$dispatch('toggle-category-form')">Add category <span aria-hidden="true">＋</span></button><a class="admin-secondary-action" href="{{ route('admin.products') }}">Manage products <span aria-hidden="true">→</span></a></div></div>
    @if($errors->has('categories'))<div class="catalogue-alert" role="alert">{{ $errors->first('categories') }}</div>@endif
    <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" @toggle-category-form.window="open = !open">
        <section class="admin-panel admin-category-create" x-show="open" x-transition x-cloak>
            <div class="admin-panel-head"><div><p class="eyebrow">NEW CATALOGUE CATEGORY</p><h2>Add a category</h2></div><button type="button" class="admin-category-form-close" @click="open = false" aria-label="Close category form">×</button></div>
            <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="admin-category-create-form">
                @csrf
                <label><span>Category name</span><input name="name" value="{{ old('name') }}" placeholder="e.g. Car Security Systems" required maxlength="190">@error('name')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label><span>URL slug <small>Optional</small></span><input name="slug" value="{{ old('slug') }}" placeholder="car-security-systems" maxlength="190">@error('slug')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label class="admin-category-description"><span>Description <small>Optional</small></span><textarea name="description" rows="3" maxlength="2000" placeholder="A short description to help customers browse this category.">{{ old('description') }}</textarea>@error('description')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label class="admin-category-upload-field"><span>Category image <small>Optional</small></span><span class="admin-category-upload-control"><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif" aria-label="Choose category image" @change="$event.target.closest('label').querySelector('[data-file-name]').textContent = $event.target.files[0]?.name || 'Choose an image'"><small data-file-name>Choose an image</small></span><small class="admin-category-image-guidance">Recommended: 1600 × 1000 px landscape (8:5), JPG, PNG, WebP or AVIF. Maximum 5 MB.</small>@error('image')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <div class="admin-category-create-actions"><button type="button" class="admin-secondary-action" @click="open = false">Cancel</button><button type="submit" class="admin-primary-action">Save category <span aria-hidden="true">→</span></button></div>
            </form>
        </section>
    </div>
    <section class="admin-panel admin-category-order">
        <div class="admin-panel-head"><div><p class="eyebrow">STOREFRONT NAVIGATION</p><h2>Category order</h2></div><span class="admin-table-result">{{ $categories->count() }} categories</span></div>
        <p class="admin-category-help">Lower numbers appear first in homepage category cards, shop filters, and navigation.</p>
        @if($categories->isNotEmpty())
            <form id="category-order-form" method="post" action="{{ route('admin.categories.order') }}">@csrf @method('PUT')</form>
            <div class="admin-category-list">
                @foreach($categories as $category)
                    @php($editingThisCategory = (string) old('category_id') === (string) $category->id)
                    <div class="admin-category-row admin-category-image-row" x-data="{ editOpen: @js($errors->any() && $editingThisCategory), selectedImage: '' }">
                        <label class="admin-category-position" for="category-order-{{ $category->id }}"><small>ORDER</small><input form="category-order-form" id="category-order-{{ $category->id }}" type="number" name="categories[{{ $category->id }}][position]" min="1" max="{{ $categories->count() }}" step="1" value="{{ old('categories.'.$category->id.'.position', $category->sort_order ?: $loop->iteration) }}" required></label>
                        <input form="category-order-form" type="hidden" name="categories[{{ $category->id }}][id]" value="{{ $category->id }}">
                        <form method="post" action="{{ route('admin.categories.image', $category->id) }}" enctype="multipart/form-data" class="admin-category-image-form" x-data="{ selectedName: '' }">@csrf @method('PATCH')
                            <span class="admin-category-thumb admin-category-thumb-edit" title="Current image for {{ $category->name }}">
                                @if($category->image)<img loading="lazy" decoding="async" src="{{ \App\Support\MediaUrl::category($category->image) }}" alt="Current {{ $category->name }} category image">@else<span aria-hidden="true">♫</span>@endif
                                <span class="admin-category-thumb-edit-icon" aria-hidden="true">✎</span>
                            </span>
                            <div class="admin-category-image-actions"><label class="admin-category-image-pick">{{ $category->image ? 'Change image' : 'Add image' }}<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif" required aria-label="Choose category image for {{ $category->name }}" @change="selectedName = $event.target.files[0]?.name || ''"></label><small x-show="selectedName" x-text="selectedName" class="admin-category-file-name"></small><button x-show="selectedName" x-cloak type="submit" class="admin-category-image-save">Save image</button><small class="admin-category-image-guidance">Recommended: 1600 × 1000 px landscape (8:5) for category cards.</small></div>
                            @error('image')<small class="admin-field-error">{{ $message }}</small>@enderror
                        </form>
                        <span class="admin-category-name"><strong>{{ $category->name }}</strong><small>{{ number_format($category->product_count) }} products · {{ $category->slug === 'car-speakers' ? '/car-speakers' : '/products/'.$category->slug }}</small></span>
                        <div class="admin-category-row-actions"><button type="button" class="admin-category-edit-button" @click="editOpen = true">Edit details</button><a href="{{ $category->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $category->slug) }}" target="_blank" rel="noopener">Preview ↗</a></div>
                        <div class="admin-category-edit-backdrop" x-show="editOpen" x-transition.opacity @click.self="editOpen = false" @keydown.escape.window="editOpen = false" x-cloak>
                            <section class="admin-category-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="category-edit-title-{{ $category->id }}" @click.stop>
                                <div class="admin-panel-head"><div><p class="eyebrow">CATEGORY DETAILS</p><h2 id="category-edit-title-{{ $category->id }}">Edit {{ $category->name }}</h2></div><button type="button" class="admin-category-form-close" @click="editOpen = false" aria-label="Close edit category form">×</button></div>
                                <form method="post" action="{{ route('admin.categories.update', $category->id) }}" enctype="multipart/form-data" class="admin-category-create-form">
                                    @csrf @method('PATCH')<input type="hidden" name="category_id" value="{{ $category->id }}">
                                    <label><span>Category name</span><input name="name" value="{{ $editingThisCategory ? old('name', $category->name) : $category->name }}" required maxlength="190">
                                        @if($editingThisCategory)
                                            @error('name')<small class="admin-field-error">{{ $message }}</small>@enderror
                                        @endif
                                    </label>
                                    <label><span>URL slug</span><input name="slug" value="{{ $editingThisCategory ? old('slug', $category->slug) : $category->slug }}" required maxlength="190" pattern="[A-Za-z0-9_-]+">
                                        @if($editingThisCategory)
                                            @error('slug')<small class="admin-field-error">{{ $message }}</small>@enderror
                                        @endif
                                    </label>
                                    <label class="admin-category-description"><span>Description <small>Optional</small></span><textarea name="description" rows="3" maxlength="2000" placeholder="A short description to help customers browse this category.">{{ $editingThisCategory ? old('description', $category->description) : $category->description }}</textarea>
                                        @if($editingThisCategory)
                                            @error('description')<small class="admin-field-error">{{ $message }}</small>@enderror
                                        @endif
                                    </label>
                                    <label class="admin-category-upload-field"><span>Replace category image <small>Optional</small></span><span class="admin-category-upload-control"><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif" @change="selectedImage = $event.target.files[0]?.name || ''"><small x-text="selectedImage || 'Keep current image or choose a new one'"></small></span><small class="admin-category-image-guidance">Recommended: 1600 × 1000 px landscape (8:5), maximum 5 MB.</small>
                                        @if($editingThisCategory)
                                            @error('image')<small class="admin-field-error">{{ $message }}</small>@enderror
                                        @endif
                                    </label>
                                    <div class="admin-category-create-actions"><button type="button" class="admin-secondary-action" @click="editOpen = false">Cancel</button><button type="submit" class="admin-primary-action">Save changes <span aria-hidden="true">→</span></button></div>
                                </form>
                            </section>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="admin-category-actions"><span>Use every position once, from 1 to {{ $categories->count() }}.</span><button form="category-order-form" type="submit" class="admin-primary-action">Save category order <span aria-hidden="true">→</span></button></div>
        @else
            <div class="admin-directory-empty"><strong>No categories yet</strong><p>Categories appear here once they are added to the catalogue.</p></div>
        @endif
    </section>
</div>
@endsection

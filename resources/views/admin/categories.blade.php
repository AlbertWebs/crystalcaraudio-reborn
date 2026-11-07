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
            <form method="post" action="{{ route('admin.categories.store') }}" class="admin-category-create-form">
                @csrf
                <label><span>Category name</span><input name="name" value="{{ old('name') }}" placeholder="e.g. Car Security Systems" required maxlength="190">@error('name')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label><span>URL slug <small>Optional</small></span><input name="slug" value="{{ old('slug') }}" placeholder="car-security-systems" maxlength="190">@error('slug')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <label class="admin-category-description"><span>Description <small>Optional</small></span><textarea name="description" rows="3" maxlength="2000" placeholder="A short description to help customers browse this category.">{{ old('description') }}</textarea>@error('description')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
                <div class="admin-category-create-actions"><button type="button" class="admin-secondary-action" @click="open = false">Cancel</button><button type="submit" class="admin-primary-action">Save category <span aria-hidden="true">→</span></button></div>
            </form>
        </section>
    </div>
    <section class="admin-panel admin-category-order">
        <div class="admin-panel-head"><div><p class="eyebrow">STOREFRONT NAVIGATION</p><h2>Category order</h2></div><span class="admin-table-result">{{ $categories->count() }} categories</span></div>
        <p class="admin-category-help">Lower numbers appear first in homepage category cards, shop filters, and navigation.</p>
        @if($categories->isNotEmpty())
            <form method="post" action="{{ route('admin.categories.order') }}">@csrf @method('PUT')
                <div class="admin-category-list">
                    @foreach($categories as $category)
                        <div class="admin-category-row"><label class="admin-category-position" for="category-order-{{ $category->id }}"><small>ORDER</small><input id="category-order-{{ $category->id }}" type="number" name="categories[{{ $category->id }}][position]" min="1" max="{{ $categories->count() }}" step="1" value="{{ old('categories.'.$category->id.'.position', $category->sort_order ?: $loop->iteration) }}" required></label><input type="hidden" name="categories[{{ $category->id }}][id]" value="{{ $category->id }}"><span class="admin-category-thumb">@if($category->image)<img loading="lazy" decoding="async" src="{{ str_starts_with($category->image, 'http') ? $category->image : \App\Support\MediaUrl::url('uploads/'.$category->image) }}" alt="">@else<span aria-hidden="true">♫</span>@endif</span><span class="admin-category-name"><strong>{{ $category->name }}</strong><small>{{ number_format($category->product_count) }} products · /products/{{ $category->slug }}</small></span><a href="{{ route('product-category', $category->slug) }}" target="_blank" rel="noopener">Preview ↗</a></div>
                    @endforeach
                </div>
                <div class="admin-category-actions"><span>Use every position once, from 1 to {{ $categories->count() }}.</span><button type="submit" class="admin-primary-action">Save category order <span aria-hidden="true">→</span></button></div>
            </form>
        @else
            <div class="admin-directory-empty"><strong>No categories yet</strong><p>Categories appear here once they are added to the catalogue.</p></div>
        @endif
    </section>
</div>
@endsection

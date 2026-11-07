@extends('admin.layout')
@section('title', 'Categories | Admin')
@section('admin_page_title', 'Categories')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-directory-page">
    <div class="admin-page-heading"><div><p class="eyebrow">STORE MANAGEMENT</p><h1>Categories</h1><p>Set how catalogue categories are ordered across the storefront.</p></div><a class="admin-primary-action" href="{{ route('admin.products') }}">Manage products <span aria-hidden="true">→</span></a></div>
    @if($errors->has('categories'))<div class="catalogue-alert" role="alert">{{ $errors->first('categories') }}</div>@endif
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

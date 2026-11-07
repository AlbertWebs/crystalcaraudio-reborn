@extends('admin.layout')

@section('title', 'Products | Admin')
@section('admin_page_title', 'Products')

@section('admin_content')
<div class="admin-dashboard admin-shell admin-catalogue">
    <div class="admin-page-heading">
        <div><p class="eyebrow">STORE MANAGEMENT</p><h1>Product catalogue</h1><p>Manage product details, pricing, stock, and how products appear in the store.</p></div>
        @can('manage-store-products')<a class="admin-primary-action" href="{{ route('admin.products.create') }}">Add product <span aria-hidden="true">＋</span></a>@endcan
    </div>

    <section class="catalogue-metrics" aria-label="Catalogue summary">
        <article><span>Total products</span><strong>{{ number_format($totalProducts) }}</strong><small>Across your catalogue</small></article>
        <article class="is-warning"><span>Missing price</span><strong>{{ number_format($zeroPriceCount) }}</strong><small>Priced at KES 0</small></article>
        <article class="is-alert"><span>Out of stock</span><strong>{{ number_format($outOfStockCount) }}</strong><small>Review availability</small></article>
        <article class="is-featured"><span>Featured</span><strong>{{ number_format($featuredCount) }}</strong><small>Shown as featured picks</small></article>
    </section>

    @if($errors->any())<div class="catalogue-alert" role="alert"><strong>Please review these fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="admin-panel catalogue-list" aria-labelledby="catalogue-list-title">
        <div class="catalogue-list-heading"><div><p class="eyebrow">ALL PRODUCTS</p><h2 id="catalogue-list-title">Catalogue</h2></div><div class="catalogue-list-summary"><span class="admin-table-result">{{ number_format($products->total()) }} results</span><label class="catalogue-page-size">Rows per page<select form="catalogue-filters-form" name="per_page" aria-label="Rows per page" onchange="this.form.requestSubmit()"><option value="20" @selected($filters['perPage'] === 20)>20</option><option value="40" @selected($filters['perPage'] === 40)>40</option><option value="80" @selected($filters['perPage'] === 80)>80</option></select></label></div></div>
        <form id="catalogue-filters-form" class="catalogue-filters" method="get" action="{{ route('admin.products') }}">
            <div class="catalogue-filter-search-row">
            <label class="catalogue-search"><span class="sr-only">Search products</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Search by name, SKU, brand, or slug"></label>
            <button class="admin-primary-action" type="submit">Apply filters <span aria-hidden="true">⌕</span></button>
            @if(request()->query())<a class="catalogue-reset" href="{{ route('admin.products') }}">Clear filters</a>@endif
            </div>
            <div class="catalogue-filter-options">
            <label><span class="sr-only">Category</span><select name="category"><option value="">All categories</option>@foreach($categories as $option)<option value="{{ $option }}" @selected($filters['category'] === $option)>{{ $option }}</option>@endforeach</select></label>
            <label><span class="sr-only">Brand</span><select name="brand"><option value="">All brands</option>@foreach($brands as $option)<option value="{{ $option }}" @selected($filters['brand'] === $option)>{{ $option }}</option>@endforeach</select></label>
            <label><span class="sr-only">Stock status</span><select name="stock"><option value="">Any stock status</option><option value="In Stock" @selected($filters['stock'] === 'In Stock')>In stock</option><option value="Out of Stock" @selected($filters['stock'] === 'Out of Stock')>Out of stock</option><option value="Pre-order" @selected($filters['stock'] === 'Pre-order')>Pre-order</option></select></label>
            <label><span class="sr-only">Price status</span><select name="price"><option value="">Any price</option><option value="zero" @selected($filters['priceState'] === 'zero')>Missing price</option><option value="priced" @selected($filters['priceState'] === 'priced')>Has a price</option></select></label>
            <label><span class="sr-only">Sort products</span><select name="sort"><option value="recent" @selected($filters['sort'] === 'recent')>Recently updated</option><option value="name" @selected($filters['sort'] === 'name')>Name A–Z</option><option value="price_low" @selected($filters['sort'] === 'price_low')>Price: low to high</option><option value="price_high" @selected($filters['sort'] === 'price_high')>Price: high to low</option><option value="stock_low" @selected($filters['sort'] === 'stock_low')>Lowest stock</option></select></label>
            </div>
        </form>

        <div class="admin-table-wrap catalogue-table-wrap">
            <table class="admin-table catalogue-table">
                <thead><tr><th>Product</th><th>Category / brand</th><th>Price</th><th>Inventory</th><th>Store labels</th><th>Updated</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td data-label="Product"><div class="catalogue-product"><span class="catalogue-product-image">@if($product->image)<img loading="lazy" decoding="async" src="{{ str_starts_with($product->image, 'http') ? $product->image : \App\Support\MediaUrl::url('uploads/'.ltrim($product->image, '/')) }}" alt="">@else<span aria-hidden="true">♫</span>@endif</span><span><strong>{{ $product->name }}</strong><small>{{ $product->sku ?: $product->slug }}</small></span></div></td>
                        <td data-label="Category / brand"><strong>{{ $product->category ?: 'Uncategorized' }}</strong><small class="catalogue-subline">{{ $product->brand ?: 'No brand' }}</small></td>
                        <td data-label="Price"><strong class="catalogue-price">KES {{ number_format((float) $product->price) }}</strong>@if($product->original_price && $product->original_price > $product->price)<small class="catalogue-old-price">KES {{ number_format((float) $product->original_price) }}</small>@endif @if((float) $product->price === 0.0)<small class="catalogue-missing-price">Price needed</small>@endif</td>
                        <td data-label="Inventory"><span class="catalogue-stock {{ strtolower(str_replace(' ', '-', $product->stock ?? '')) }}">{{ $product->stock ?: 'Availability not set' }}</span><small class="catalogue-subline">{{ $product->stock_quantity === null ? 'Quantity not tracked' : number_format($product->stock_quantity).' units' }}@if($product->stock_quantity !== null && $product->stock_quantity <= ($product->low_stock_threshold ?? 5) && $product->stock_quantity > 0) · Low stock @endif</small></td>
                        <td data-label="Store labels"><div class="catalogue-labels">@if($product->featured)<span>Featured</span>@endif @if($product->is_offer)<span>Offer</span>@endif @if($product->is_special)<span>Special</span>@endif @if($product->trending)<span>Trending</span>@endif @if(!$product->featured && !$product->is_offer && !$product->is_special && !$product->trending)<small>—</small>@endif</div></td>
                        <td data-label="Updated">{{ $product->updated_at?->format('j M Y') }}</td>
                        <td data-label="Actions"><div class="catalogue-row-actions"><a href="{{ route('admin.products.edit', $product) }}" aria-label="Edit {{ $product->name }}">Edit</a><form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from the catalogue?')">@csrf @method('DELETE')<button type="submit" aria-label="Delete {{ $product->name }}">Delete</button></form></div></td>
                    </tr>
                @empty<tr><td class="table-empty" data-label="" colspan="7">No products match these filters. <a href="{{ route('admin.products') }}">Clear filters</a>@can('manage-store-products') or <a href="{{ route('admin.products.create') }}">add a product</a>@endcan.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())@include('admin.partials.catalogue-pagination', ['paginator' => $products])@endif
    </section>

</div>
<script>if (window.location.hash === '#add-product') window.location.replace(@json(route('admin.products.create')));</script>
@endsection

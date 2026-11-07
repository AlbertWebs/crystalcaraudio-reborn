@extends('admin.layout')
@section('title', 'Brands | Admin')
@section('admin_page_title', 'Brands')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-directory-page">
    <div class="admin-page-heading"><div><p class="eyebrow">STORE MANAGEMENT</p><h1>Brands</h1><p>Review brands currently assigned to products. Update brand names from the product editor.</p></div><a class="admin-primary-action" href="{{ route('admin.products.create') }}">Add product <span aria-hidden="true">＋</span></a></div>
    <section class="admin-panel directory-panel">
        <div class="directory-panel-heading"><div><p class="eyebrow">PRODUCT BRANDS</p><h2>Brand directory</h2></div><span class="admin-table-result">{{ number_format($brands->total()) }} brands</span></div>
        <form class="directory-search" method="get" action="{{ route('admin.brands') }}"><label><span class="sr-only">Search brands</span><input type="search" name="q" value="{{ $search }}" placeholder="Search brands"></label><button class="admin-primary-action" type="submit">Search</button>@if($search !== '')<a class="catalogue-reset" href="{{ route('admin.brands') }}">Clear</a>@endif</form>
        @if($brands->isNotEmpty())<div class="directory-brand-grid">@foreach($brands as $brand)<article class="directory-brand-card"><span class="directory-brand-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($brand->brand, 0, 1)) }}</span><div><h3>{{ $brand->brand }}</h3><p>{{ number_format($brand->product_count) }} {{ \Illuminate\Support\Str::plural('product', $brand->product_count) }}</p>@if($brand->zero_price_count)<small>{{ number_format($brand->zero_price_count) }} need pricing</small>@endif</div><a href="{{ route('admin.products', ['brand' => $brand->brand]) }}" aria-label="View {{ $brand->brand }} products">View products <span aria-hidden="true">→</span></a></article>@endforeach</div><div class="catalogue-pagination">{{ $brands->links() }}</div>@else<div class="admin-directory-empty"><strong>No brands found</strong><p>Add or search brands from your product catalogue.</p></div>@endif
    </section>
</div>
@endsection

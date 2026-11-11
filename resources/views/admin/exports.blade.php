@extends('admin.layout')
@section('title', 'Product exports | Admin')
@section('admin_page_title', 'Product exports')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-export-page">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">CATALOGUE TOOLS</p>
            <h1>Product exports</h1>
            <p>Download a filtered Google Merchant Center feed or export your full catalogue for spreadsheets.</p>
        </div>
        <a class="admin-primary-action" href="{{ route('admin.products') }}">Manage catalogue <span aria-hidden="true">→</span></a>
    </div>

    <div class="admin-export-stats" aria-label="Catalogue export summary">
        <article><span class="admin-export-stat-icon" aria-hidden="true">▧</span><div><small>Catalogue products</small><strong>{{ number_format($totalProducts) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-teal" aria-hidden="true">✓</span><div><small>Passes feed checks</small><strong>{{ number_format($merchantEligible) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-amber" aria-hidden="true">!</span><div><small>Needs feed details</small><strong>{{ number_format($merchantSkipped) }}</strong></div></article>
    </div>

    <section class="admin-export-grid" aria-label="Download catalogue exports">
        <article class="admin-export-card admin-export-card-featured">
            <div class="admin-export-card-top"><span class="admin-export-card-icon" aria-hidden="true">G</span><span class="admin-export-format">Google Merchant Center</span></div>
            <h2>Google product feed</h2>
            <p>CSV containing only products that pass the required feed data checks for title, description, image, price, stock status, category, brand and manufacturer part number.</p>
            <ul><li>Includes {{ number_format($merchantEligible) }} products that meet the feed checks</li><li>Uses the product code as the manufacturer part number (MPN)</li><li>Product links use crystalcaraudio.com and prices are formatted in KES</li></ul>
            <a class="admin-primary-action" href="{{ route('admin.exports.download', 'merchant') }}">Download Merchant feed <span aria-hidden="true">↓</span></a>
            <small class="admin-export-footnote">Google still checks product and image links, price and stock accuracy, and account policies after upload.</small>
        </article>

        <article class="admin-export-card">
            <div class="admin-export-card-top"><span class="admin-export-card-icon is-blue" aria-hidden="true">CSV</span><span class="admin-export-format">Comma-separated values</span></div>
            <h2>Catalogue CSV</h2>
            <p>Export every product, including items that still need pricing or images, for bulk review and editing.</p>
            <ul><li>Opens in Excel, Google Sheets and other spreadsheet apps</li><li>Includes stock, SKU, product and image URLs</li><li>UTF-8 encoded for clear product names</li></ul>
            <a class="admin-secondary-action" href="{{ route('admin.exports.download', 'csv') }}">Download CSV <span aria-hidden="true">↓</span></a>
        </article>

        <article class="admin-export-card">
            <div class="admin-export-card-top"><span class="admin-export-card-icon is-green" aria-hidden="true">XLS</span><span class="admin-export-format">Microsoft Excel workbook</span></div>
            <h2>Catalogue Excel</h2>
            <p>Download the full catalogue as an Excel workbook, with prices and quantities available as numeric cells.</p>
            <ul><li>Includes all products and their current stock details</li><li>Price and quantity columns are ready to sort or calculate</li><li>Saved as a native .xlsx file</li></ul>
            <a class="admin-secondary-action" href="{{ route('admin.exports.download', 'excel') }}">Download Excel file <span aria-hidden="true">↓</span></a>
        </article>
    </section>

    @if($merchantSkipped > 0)
        <aside class="admin-export-note"><span aria-hidden="true">i</span><p><strong>{{ number_format($merchantSkipped) }} products are excluded from the Merchant feed.</strong> Add complete product details, a valid brand and a manufacturer part number, then check that the price, stock status and image are accurate. You can update products from <a href="{{ route('admin.products') }}">catalogue management</a>.</p></aside>
    @endif

    <section class="admin-export-issues" aria-labelledby="merchant-products-needing-updates">
        <div class="admin-export-issues-heading">
            <div>
                <h2 id="merchant-products-needing-updates">Products needing updates</h2>
                <p>{{ number_format($excludedProducts->total()) }} products are excluded. Each row lists the exact feed checks that need attention.</p>
            </div>
        </div>

        @if($excludedProducts->count() > 0)
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th scope="col">Product</th><th scope="col">Current feed details</th><th scope="col">Why it is excluded</th><th scope="col">Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($excludedProducts as $item)
                            <tr>
                                <td data-label="Product"><strong>{{ $item['product']->name }}</strong><small>Product ID {{ $item['product']->id }}</small></td>
                                <td data-label="Current feed details"><strong>{{ $item['product']->brand ?: 'No brand' }}</strong><small>Product code: {{ $item['product']->sku ?: 'Missing' }}</small><small>Category: {{ $item['product']->category ?: 'Missing' }}</small><small>Price: KES {{ number_format((float) $item['product']->price, 2) }}</small></td>
                                <td data-label="Why it is excluded"><ul class="admin-export-reasons">@foreach($item['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></td>
                                <td data-label="Action"><a class="admin-export-edit-link" href="{{ route('admin.products.edit', $item['product']) }}">Update product <span aria-hidden="true">→</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($excludedProducts->hasPages())
                @include('admin.partials.catalogue-pagination', ['paginator' => $excludedProducts])
            @endif
        @else
            <p class="admin-export-issues-empty">All products currently pass the Merchant feed data checks.</p>
        @endif
    </section>
</div>
@endsection

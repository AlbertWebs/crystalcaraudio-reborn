@extends('admin.layout')
@section('title', 'Product exports | Admin')
@section('admin_page_title', 'Product exports')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-export-page">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">CATALOGUE TOOLS</p>
            <h1>Product exports</h1>
            <p>Download a current product feed for Google Merchant Center or export your full catalogue for spreadsheets.</p>
        </div>
        <a class="admin-primary-action" href="{{ route('admin.products') }}">Manage catalogue <span aria-hidden="true">→</span></a>
    </div>

    <div class="admin-export-stats" aria-label="Catalogue export summary">
        <article><span class="admin-export-stat-icon" aria-hidden="true">▧</span><div><small>Catalogue products</small><strong>{{ number_format($totalProducts) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-teal" aria-hidden="true">✓</span><div><small>Merchant feed ready</small><strong>{{ number_format($merchantEligible) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-amber" aria-hidden="true">!</span><div><small>Needs feed details</small><strong>{{ number_format($merchantSkipped) }}</strong></div></article>
    </div>

    <section class="admin-export-grid" aria-label="Download catalogue exports">
        <article class="admin-export-card admin-export-card-featured">
            <div class="admin-export-card-top"><span class="admin-export-card-icon" aria-hidden="true">G</span><span class="admin-export-format">Google Merchant Center</span></div>
            <h2>Google product feed</h2>
            <p>Merchant-ready CSV with product titles, descriptions, URLs, image links, price, stock availability, brand, category and sale price.</p>
            <ul><li>Includes {{ number_format($merchantEligible) }} products with required details</li><li>Prices are formatted in KES</li><li>Products without a price, image or description are skipped</li></ul>
            <a class="admin-primary-action" href="{{ route('admin.exports.download', 'merchant') }}">Download Merchant feed <span aria-hidden="true">↓</span></a>
            <small class="admin-export-footnote">Google must be able to access each product and image URL. Keep image links publicly crawlable.</small>
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
        <aside class="admin-export-note"><span aria-hidden="true">i</span><p><strong>{{ number_format($merchantSkipped) }} products are not in the Merchant feed.</strong> Add a price, product description and main image to include them. You can update products from <a href="{{ route('admin.products') }}">catalogue management</a>.</p></aside>
    @endif
</div>
@endsection

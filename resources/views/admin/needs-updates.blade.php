@extends('admin.layout')
@section('title', 'Products needing updates | Admin')
@section('admin_page_title', 'Products needing updates')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-export-page">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">CATALOGUE QUALITY</p>
            <h1>Products needing updates</h1>
            <p>Products with missing or invalid details that prevent them from passing the Google Merchant feed checks.</p>
        </div>
        <a class="admin-primary-action" href="{{ route('admin.exports') }}">Product exports <span aria-hidden="true">→</span></a>
    </div>

    <div class="admin-export-stats" aria-label="Product update summary">
        <article><span class="admin-export-stat-icon is-amber" aria-hidden="true">!</span><div><small>Products needing updates</small><strong>{{ number_format($merchantSkipped) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-blue" aria-hidden="true">↻</span><div><small>Products on this page</small><strong>{{ number_format($excludedProducts->count()) }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-teal" aria-hidden="true">✎</span><div><small>Update products</small><strong><a href="{{ route('admin.products') }}">Open catalogue →</a></strong></div></article>
    </div>

    <section class="admin-export-issues" aria-labelledby="needs-updates-heading">
        <div class="admin-export-issues-heading">
            <div>
                <h2 id="needs-updates-heading">Feed checks requiring attention</h2>
                <p>{{ number_format($merchantSkipped) }} products are excluded. Each row lists the exact checks that need attention.</p>
            </div>
        </div>
        @include('admin.partials.products-needing-updates-table')
    </section>
</div>
@endsection

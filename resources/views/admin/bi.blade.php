@extends('admin.layout')
@section('title', 'Business intelligence | Crystal Car Audio Admin')
@section('admin_page_title', 'Business intelligence')
@section('admin_content')
@php
    $maxRevenue = max(1, collect($report['series'])->max('value') ?? 0);
    $previousNet = $report['previous_period']['net_sales'];
    $salesChange = $previousNet > 0 ? (($report['net_sales'] - $previousNet) / $previousNet) * 100 : null;
    $refundRate = $report['orders'] > 0 ? ($report['refunded_orders'] / $report['orders']) * 100 : null;
    $exportQuery = array_merge(request()->query(), ['export' => 'csv']);
@endphp
<div class="admin-dashboard admin-bi-page">
    <div class="admin-page-heading"><div><p class="admin-eyebrow">BUSINESS INTELLIGENCE</p><h1>See how the store is performing.</h1><p>Sales and customer insights calculated from recorded orders.</p></div><div class="admin-heading-actions"><a class="admin-secondary-action" href="{{ route('admin.bi', $exportQuery) }}">Download CSV <span aria-hidden="true">↓</span></a></div></div>

    <form class="admin-bi-filters" method="get" action="{{ route('admin.bi') }}">
        <label><span>Date range</span><select name="period"><option value="7d" @selected($filters['period'] === '7d')>Last 7 days</option><option value="30d" @selected($filters['period'] === '30d')>Last 30 days</option><option value="this_month" @selected($filters['period'] === 'this_month')>This month</option><option value="last_month" @selected($filters['period'] === 'last_month')>Last month</option><option value="this_year" @selected($filters['period'] === 'this_year')>This year</option><option value="custom" @selected($filters['period'] === 'custom')>Custom range</option></select></label>
        <label><span>From</span><input type="date" name="from" value="{{ $filters['from'] }}"></label>
        <label><span>To</span><input type="date" name="to" value="{{ $filters['to'] }}"></label>
        <label><span>Category</span><select name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>@endforeach</select></label>
        <label><span>Brand</span><select name="brand"><option value="">All brands</option>@foreach($brands as $brand)<option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>@endforeach</select></label>
        <label><span>Payment method</span><select name="payment_method"><option value="">All methods</option>@foreach($paymentMethods as $method)<option value="{{ $method }}" @selected($filters['payment_method'] === $method)>{{ str_replace('_', ' ', ucfirst($method)) }}</option>@endforeach</select></label>
        <label><span>Order status</span><select name="order_status"><option value="">All statuses</option>@foreach(['pending','processing','shipped','completed','cancelled','refunded'] as $status)<option value="{{ $status }}" @selected($filters['order_status'] === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
        <div class="admin-bi-filter-actions"><button class="admin-primary-action" type="submit">Apply filters <span aria-hidden="true">→</span></button><a href="{{ route('admin.bi') }}">Reset</a></div>
    </form>
    @if($errors->any())<div class="admin-global-notice is-error" role="alert">{{ $errors->first() }}</div>@endif
    @if(($filters['category'] !== '' || $filters['brand'] !== '') && ($report['attribution_coverage'] ?? 0) < 100)<div class="admin-data-note admin-bi-data-warning">Some older orders have no product category or brand snapshot. Category and brand results include only order lines where this information was recorded.</div>@endif

    <div class="admin-bi-kpis">
        <article class="admin-bi-kpi"><span>Gross sales</span><strong>KES {{ number_format($report['gross_sales']) }}</strong><small>Before recorded discounts</small></article>
        <article class="admin-bi-kpi"><span>Net sales</span><strong>KES {{ number_format($report['net_sales']) }}</strong><small>@if($salesChange !== null)<b class="{{ $salesChange >= 0 ? 'positive' : 'negative' }}">{{ $salesChange >= 0 ? '+' : '' }}{{ number_format($salesChange, 1) }}%</b> vs {{ $report['previous_period']['from'] }}–{{ $report['previous_period']['to'] }}@else No previous-period baseline @endif</small></article>
        <article class="admin-bi-kpi"><span>Paid orders</span><strong>{{ number_format($report['paid_orders']) }}</strong><small>{{ number_format($report['pending_orders']) }} pending in selection</small></article>
        <article class="admin-bi-kpi"><span>Average order value</span><strong>KES {{ number_format($report['average_order_value']) }}</strong><small>Paid and escrow-held orders</small></article>
        <article class="admin-bi-kpi"><span>Discounts</span><strong>KES {{ number_format($report['discounts']) }}</strong><small>Recorded discounts on paid orders</small></article>
        <article class="admin-bi-kpi"><span>Refunds</span><strong>KES {{ number_format($report['refunds']) }}</strong><small>{{ $refundRate !== null ? number_format($refundRate, 1).'% of orders' : 'No orders in range' }}</small></article>
        <article class="admin-bi-kpi"><span>Repeat customer rate</span><strong>{{ $report['repeat_customer_rate'] !== null ? number_format($report['repeat_customer_rate'], 1).'%' : '—' }}</strong><small>{{ number_format($report['distinct_customers']) }} paying customers in range</small></article>
        <article class="admin-bi-kpi {{ $report['profit_available'] ? '' : 'admin-bi-kpi-muted' }}"><span>Gross profit</span><strong>{{ $report['profit_available'] ? 'KES '.number_format($report['gross_profit']) : 'Unavailable' }}</strong><small>{{ $report['profit_available'] ? number_format($report['gross_margin'], 1).'% gross margin' : (($report['cost_coverage'] ?? 0) > 0 ? number_format($report['cost_coverage'], 1).'% of order lines have unit costs' : 'Product unit costs are not recorded.') }}</small></article>
    </div>

    <div class="admin-bi-grid">
        <section class="admin-panel admin-bi-chart-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">REVENUE TREND</p><h2>Net sales over time</h2></div><span class="admin-chart-period">{{ $filters['from'] }} — {{ $filters['to'] }}</span></div>
            @if(collect($report['series'])->sum('value') > 0)<div class="admin-bi-chart" role="img" aria-label="Net sales over the selected period"><div class="admin-bi-bars">@foreach($report['series'] as $point)<div class="admin-bi-bar" title="{{ $point['label'] }} · KES {{ number_format($point['value']) }}"><span style="height:{{ max(3, ($point['value'] / $maxRevenue) * 100) }}%"></span><small>{{ $loop->iteration === 1 || $loop->last || $loop->iteration % max(1, (int) ceil(count($report['series']) / 8)) === 0 ? $point['label'] : '' }}</small></div>@endforeach</div></div>@else<div class="admin-bi-empty">There are no paid orders in this date range.</div>@endif
            <div class="admin-chart-foot"><span>Revenue counts paid and eConfirm escrow-held orders. Cancelled and refunded orders are excluded.</span></div>
        </section>
        <section class="admin-panel admin-bi-payment-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">COLLECTIONS</p><h2>Payment methods</h2></div></div>
            @if(count($report['payment_methods']))<div class="admin-bi-list">@foreach($report['payment_methods'] as $method => $count)<div class="admin-bi-list-row"><span>{{ str_replace('_', ' ', ucfirst($method)) }}</span><strong>{{ number_format($count) }} orders</strong></div>@endforeach</div>@else<div class="admin-bi-empty">No paid orders for this selection.</div>@endif
            <p class="admin-data-note">Order totals represent collected amounts recorded by the store. Taxes are not separately tracked.</p>
        </section>
    </div>

    <div class="admin-bi-grid admin-bi-detail-grid">
        <section class="admin-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">PRODUCT INTELLIGENCE</p><h2>Top products by recorded sales</h2></div></div>
            @if(count($report['top_products']))<div class="admin-bi-list">@foreach($report['top_products'] as $name => $item)<div class="admin-bi-list-row"><span><strong>{{ $name }}</strong><small>{{ number_format($item['quantity']) }} units</small></span><b>KES {{ number_format($item['revenue']) }}</b></div>@endforeach</div>@else<div class="admin-bi-empty">Product-level sales are not available for this selection.</div>@endif
        </section>
        <section class="admin-panel" id="brands"><div class="admin-panel-head"><div><p class="admin-eyebrow">CATEGORY &amp; BRAND</p><h2>Recorded product attribution</h2></div></div>
            @if($report['attribution_coverage'] !== null)<div class="admin-attribution-meter"><span style="width:{{ min(100, $report['attribution_coverage']) }}%"></span></div><p class="admin-attribution-copy">{{ number_format($report['attribution_coverage'], 1) }}% of order lines include a category snapshot.</p>@else<p class="admin-bi-empty">No product-level category snapshots have been recorded yet.</p>@endif
            <div class="admin-bi-subsection"><h3>Sales by category</h3>@forelse($report['category_sales'] as $category => $sales)<div class="admin-bi-list-row"><span>{{ $category }}</span><strong>KES {{ number_format($sales) }}</strong></div>@empty<p class="admin-bi-empty">Category sales will appear as orders record product categories.</p>@endforelse</div>
            <div class="admin-bi-subsection"><h3>Sales by brand</h3>@forelse($report['brand_sales'] as $brand => $sales)<div class="admin-bi-list-row"><span>{{ $brand }}</span><strong>KES {{ number_format($sales) }}</strong></div>@empty<p class="admin-bi-empty">Brand sales will appear for order lines with a recorded brand.</p>@endforelse</div>
        </section>
    </div>
    <p class="admin-bi-method-note">Comparisons use the same number of immediately preceding calendar days. Profit is shown only when every paid order line in the selection has a recorded unit cost. Installation revenue and checkout conversion are not estimated because those events are not yet recorded.</p>
</div>
@endsection

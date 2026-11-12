@extends('admin.layout')
@section('title', 'Dashboard | Crystal Car Audio Admin')
@section('admin_page_title', 'Dashboard')
@section('admin_content')
<section class="admin-dashboard admin-shell">
    <div class="admin-page-heading"><div><h1>Dashboard</h1><p>Welcome back, {{ Str::before(auth()->user()->name, ' ') }}. Here’s what’s happening at Crystal Car Audio today.</p></div><div class="admin-heading-actions"><span class="admin-date-chip"><span aria-hidden="true">▦</span> Today · {{ now()->format('j M Y') }}</span><a class="admin-primary-action" href="{{ route('admin.products.create') }}">Add product <span aria-hidden="true">＋</span></a></div></div>
    <div class="admin-kpi-grid" aria-label="Store performance summary">
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-revenue">▤</span><span class="admin-kpi-label">Total revenue</span><strong>KES {{ number_format($revenue) }}</strong><small class="{{ $revenueGrowth !== null ? ($revenueGrowth >= 0 ? 'positive' : 'negative') : '' }}">{{ $revenueGrowth !== null ? number_format($revenueGrowth, 1).'% vs last month' : 'Paid and escrow-held orders' }}</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-orders">▣</span><span class="admin-kpi-label">Total orders</span><strong>{{ number_format($ordersCount) }}</strong><small>All time</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-pending">◷</span><span class="admin-kpi-label">Pending orders</span><strong>{{ number_format($pendingCount) }}</strong><small>Need attention</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-products">◇</span><span class="admin-kpi-label">Total products</span><strong>{{ number_format($productsCount) }}</strong><small>In the catalogue</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-customers">♙</span><span class="admin-kpi-label">Total customers</span><strong>{{ number_format($customersCount) }}</strong><small>Registered accounts</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-bookings">▦</span><span class="admin-kpi-label">Installation bookings</span><strong>{{ number_format($installationBookingsCount) }}</strong><small>{{ number_format($pendingInstallationsCount) }} awaiting confirmation</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-stock">!</span><span class="admin-kpi-label">Low stock items</span><strong>{{ number_format($lowStockCount) }}</strong><small>{{ number_format($outOfStockCount) }} out of stock</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-average">★</span><span class="admin-kpi-label">Average order value</span><strong>KES {{ number_format($averageOrderValue) }}</strong><small>Paid and escrow-held orders</small></article>
    </div>
    @php
        $chartMax = max(1, (float) $revenueSeries->max('value'));
        $chartPoints = $revenueSeries->values()->map(function ($point, $index) use ($chartMax, $revenueSeries) {
            $x = $revenueSeries->count() > 1 ? 18 + ($index / ($revenueSeries->count() - 1)) * 664 : 350;
            $y = 178 - (($point['value'] / $chartMax) * 142);
            return round($x, 1).','.round($y, 1);
        });
        $categoryColors = ['#08716d', '#16a89d', '#58c8c0', '#9ce2dc', '#ccefeb'];
        $categoryTotal = array_sum($salesByCategory);
        $categorySalesLabel = $categoryTotal >= 1000000 ? 'KES '.number_format($categoryTotal / 1000000, 2).'M' : 'KES '.number_format($categoryTotal);
        $categoryStops = [];
        $categoryOffset = 0;
        $categoryIndex = 0;
        foreach (array_slice($salesByCategory, 0, 5, true) as $amount) {
            $nextOffset = $categoryOffset + ($categoryTotal > 0 ? ($amount / $categoryTotal) * 100 : 0);
            $categoryStops[] = $categoryColors[$categoryIndex % count($categoryColors)].' '.$categoryOffset.'% '.$nextOffset.'%';
            $categoryOffset = $nextOffset;
            $categoryIndex++;
        }
        $categoryDonut = $categoryStops ? 'conic-gradient('.implode(',', $categoryStops).')' : 'conic-gradient(#e7efec 0% 100%)';
    @endphp
    <div class="admin-overview-grid">
        <section class="admin-panel admin-revenue-panel" aria-labelledby="admin-revenue-title">
            <div class="admin-panel-head"><div><h2 id="admin-revenue-title">Revenue performance</h2><p>Paid and escrow-held orders · last 14 days</p></div><a class="admin-chart-period" href="{{ route('admin.bi') }}">View report <span aria-hidden="true">↗</span></a></div>
            <div class="admin-line-chart" role="img" aria-label="Revenue by day over the last 14 days">
                <div class="admin-chart-y-axis"><span>KES {{ number_format($chartMax) }}</span><span>KES {{ number_format($chartMax * .66) }}</span><span>KES {{ number_format($chartMax * .33) }}</span><span>KES 0</span></div>
                <svg viewBox="0 0 700 205" preserveAspectRatio="none" aria-hidden="true">
                    <defs><linearGradient id="admin-chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#29b8aa" stop-opacity=".35"/><stop offset="100%" stop-color="#29b8aa" stop-opacity=".02"/></linearGradient></defs>
                    <path class="admin-chart-area" d="M {{ $chartPoints->first() ?? '18,178' }} L {{ $chartPoints->implode(' L ') }} L 682,178 Z"/>
                    <polyline class="admin-chart-line" points="{{ $chartPoints->implode(' ') }}"/>
                    @foreach($chartPoints as $index => $point)<circle class="admin-chart-point" cx="{{ explode(',', $point)[0] }}" cy="{{ explode(',', $point)[1] }}" r="3.5"><title>{{ $revenueSeries->values()[$index]['date'] }} · KES {{ number_format($revenueSeries->values()[$index]['value']) }}</title></circle>@endforeach
                </svg>
                <div class="admin-chart-x-axis">@foreach($revenueSeries as $point)<span>{{ $loop->iteration === 1 || $loop->iteration === 5 || $loop->iteration === 9 || $loop->iteration === 14 ? $point['label'] : '' }}</span>@endforeach</div>
            </div>
            <div class="admin-chart-foot"><span>Net sales are based on paid and escrow-held order totals.</span><span class="admin-chart-legend"><i></i> Revenue</span></div>
        </section>
        <section class="admin-panel admin-category-sales" aria-labelledby="admin-category-sales-title">
            <div class="admin-panel-head"><div><h2 id="admin-category-sales-title">Sales by category</h2><p>Recorded category totals from paid orders</p></div><a class="admin-chart-period" href="{{ route('admin.bi') }}">Details ↗</a></div>
            @if($categoryTotal > 0)
                <div class="admin-donut-layout"><div class="admin-category-donut" style="--donut: {{ $categoryDonut }}"><span><strong>{{ $categorySalesLabel }}</strong><small>Total sales</small></span></div>
                    <div class="admin-category-legend">@foreach(array_slice($salesByCategory, 0, 5, true) as $categoryName => $amount)<div><i style="--legend-color:{{ $categoryColors[$loop->index % count($categoryColors)] }}"></i><span>{{ $categoryName }}</span><strong>{{ round(($amount / $categoryTotal) * 100) }}%</strong></div>@endforeach</div></div>
            @else
                <div class="admin-dashboard-empty"><span>♫</span><strong>Category sales will appear here</strong><small>Paid orders with saved category details will populate this chart.</small></div>
            @endif
        </section>
    </div>
    <div class="admin-dashboard-widgets">
        @can('manage-store-orders')
            <section class="admin-panel admin-recent-orders"><div class="admin-panel-head"><h2>Recent orders</h2><a href="#orders">View all orders <span aria-hidden="true">→</span></a></div>
                @forelse($recentOrders as $order)<div class="admin-mini-order"><span><strong>{{ $order->reference }}</strong><small>{{ $order->customer_name }}</small></span><b>KES {{ number_format($order->total) }}</b><span class="admin-order-status status-{{ $order->order_status }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span><time>{{ $order->created_at->format('M j') }}</time></div>@empty<div class="admin-dashboard-empty"><strong>No orders yet</strong><small>New customer orders will show up here.</small></div>@endforelse
            </section>
        @endcan
        <section class="admin-panel admin-top-products"><div class="admin-panel-head"><h2>Top selling products</h2><a href="{{ route('admin.bi') }}">View report <span aria-hidden="true">→</span></a></div>
            @forelse($topSellingProducts as $name => $product)<div class="admin-top-product"><span class="admin-top-product-image">@if($product['image'])<img loading="lazy" decoding="async" src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : \App\Support\MediaUrl::url('uploads/'.ltrim($product['image'], '/')) }}" alt="">@else<span aria-hidden="true">♫</span>@endif</span><strong>{{ $name }}</strong><small>{{ number_format($product['quantity']) }} sold</small><b>KES {{ number_format($product['revenue']) }}</b></div>@empty<div class="admin-dashboard-empty"><strong>No sales data yet</strong><small>Top sellers appear after paid orders are placed.</small></div>@endforelse
        </section>
        <section class="admin-panel admin-quick-actions"><div class="admin-panel-head"><h2>Quick actions</h2></div><div class="admin-quick-action-grid">
            @can('manage-store-products')<a href="{{ route('admin.products.create') }}"><span>＋</span>Add product</a>@endcan
            @can('manage-store-orders')<a href="#orders"><span>▣</span>View orders</a>@endcan
            @can('manage-installations')<a href="{{ route('admin.installations') }}"><span>▦</span>New booking</a>@endcan
            @can('manage-store-inventory')<a href="{{ route('admin.inventory') }}"><span>◇</span>Manage stock</a>@endcan
            @can('view-admin-bi')<a href="{{ route('admin.bi') }}"><span>⌁</span>Sales reports</a>@endcan
        </div><div class="admin-data-note">Revenue excludes pending, cancelled and refunded orders.</div></section>
    </div>
    <div class="admin-store-health"><span class="admin-health-indicator"></span><strong>Store data is up to date</strong><small>Metrics refresh when the dashboard loads.</small><a href="{{ route('admin.bi') }}">Open business intelligence ↗</a></div>
    @if($search !== '')<div class="admin-search-summary">Showing matching products and orders for <strong>“{{ $search }}”</strong>. <a href="{{ route('admin.home') }}">Clear search</a></div>@endif
    @if($errors->has('categories'))<div class="admin-notice admin-notice-error" role="alert">{{ $errors->first('categories') }}</div>@endif
    <section class="admin-panel admin-category-order" id="categories">
        <div class="admin-panel-head"><div><p class="eyebrow">STOREFRONT NAVIGATION</p><h2>Organize categories</h2></div><p>Lower numbers appear first</p></div>
        <p class="admin-category-help">Set the display order for the homepage category cards, shop filters and main menu.</p>
        @if($categories->isNotEmpty())<form method="post" action="{{ route('admin.categories.order') }}">@csrf @method('PUT')
            <div class="admin-category-list">
                @foreach($categories as $category)
                    <div class="admin-category-row"><label class="admin-category-position" for="category-order-{{ $category->id }}"><small>ORDER</small><input id="category-order-{{ $category->id }}" type="number" name="categories[{{ $category->id }}][position]" min="1" max="{{ $categories->count() }}" step="1" value="{{ old('categories.'.$category->id.'.position', $category->sort_order ?: $loop->iteration) }}" required></label><input type="hidden" name="categories[{{ $category->id }}][id]" value="{{ $category->id }}"><span class="admin-category-thumb">@if($category->image)<img loading="lazy" decoding="async" src="{{ str_starts_with($category->image, 'http') ? $category->image : \App\Support\MediaUrl::category($category->image) }}" alt="">@else<span aria-hidden="true">♫</span>@endif</span><span class="admin-category-name"><strong>{{ $category->name }}</strong><small>{{ number_format($category->product_count) }} products</small></span><a href="{{ $category->slug === 'car-speakers' ? route('car-speakers') : route('product-category', $category->slug) }}" target="_blank" rel="noopener" aria-label="Preview {{ $category->name }}">Preview ↗</a></div>
                @endforeach
            </div>
            <div class="admin-category-actions"><span>Use each position once, from 1 to {{ $categories->count() }}.</span><button type="submit" class="button button-dark">Save category order <span aria-hidden="true">→</span></button></div>
        </form>@else<p class="admin-category-help">No categories are available yet.</p>@endif
    </section>
    <section class="admin-panel zero-price-panel" id="inventory" aria-labelledby="zero-price-title">
        <div class="admin-panel-head zero-price-heading">
            <div><p class="eyebrow">PRICING REVIEW</p><h2 id="zero-price-title">Products priced at KES 0</h2><p class="zero-price-help">Export the full list, enter prices in the new_price_kes column, then upload the CSV to update the catalogue.</p></div>
            <div class="zero-price-header-actions"><span class="zero-price-count">{{ number_format($zeroPriceProductsCount) }} products</span><a class="button button-dark zero-price-export" href="{{ route('admin.products.zero-price-prices.export') }}">Export pricing CSV <span aria-hidden="true">↓</span></a></div>
        </div>
        @if($errors->has('product_ids') || $errors->has('product_ids.*') || $errors->has('stock') || $errors->has('pricing_file'))
            <div class="admin-notice admin-notice-error" role="alert">@foreach($errors->get('pricing_file') as $error)<p>{{ $error }}</p>@endforeach{{ $errors->first('product_ids') ?: $errors->first('product_ids.*') ?: $errors->first('stock') }}</div>
        @endif
        <form class="zero-price-import-form" method="post" action="{{ route('admin.products.zero-price-prices.import') }}" enctype="multipart/form-data">
            @csrf
            <label for="zero-price-import">Upload completed pricing CSV</label>
            <input id="zero-price-import" type="file" name="pricing_file" accept=".csv,text/csv" required>
            <button class="button button-dark" type="submit">Update product prices</button>
        </form>
        @if($zeroPriceProducts->isNotEmpty())
            <form id="zero-price-stock-form" method="post" action="{{ route('admin.products.zero-price-stock') }}">
                @csrf @method('PATCH')
                <div class="zero-price-bulkbar">
                    <label for="zero-price-stock">Set stock status for selected</label>
                    <select id="zero-price-stock" name="stock" required>
                        <option value="">Choose a status</option>
                        <option value="In Stock">In Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                        <option value="Pre-order">Pre-order</option>
                    </select>
                    <span id="zero-price-selected-count" aria-live="polite">0 selected</span>
                    <button id="zero-price-update-button" class="button button-dark" type="submit" disabled>Update stock status</button>
                </div>
                <div class="admin-table-wrap zero-price-table-wrap">
                    <table class="admin-table zero-price-table">
                        <thead><tr><th><input id="zero-price-select-all" type="checkbox" aria-label="Select all zero-priced products on this page"></th><th>Product</th><th>Category</th><th>Brand</th><th>Price</th><th>Current stock</th></tr></thead>
                        <tbody>
                            @foreach($zeroPriceProducts as $product)
                                <tr>
                                    <td><input class="zero-price-product-checkbox" type="checkbox" name="product_ids[]" value="{{ $product->id }}" aria-label="Select {{ $product->name }}"></td>
                                    <td><span class="zero-price-product"><span class="zero-price-thumb">@if($product->image)<img loading="lazy" decoding="async" src="{{ str_starts_with($product->image, 'http') ? $product->image : \App\Support\MediaUrl::url('uploads/'.$product->image) }}" alt="">@else<span aria-hidden="true">♫</span>@endif</span><span><b>{{ $product->name }}</b><small>{{ $product->slug }}</small></span></span></td>
                                    <td>{{ $product->category ?: '—' }}</td>
                                    <td>{{ $product->brand ?: '—' }}</td>
                                    <td><strong class="zero-price-value">KES 0</strong></td>
                                    <td><span class="zero-price-stock">{{ $product->stock }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($zeroPriceProducts->hasPages())<div class="zero-price-pagination">{{ $zeroPriceProducts->links() }}</div>@endif
            </form>
        @else
            <div class="zero-price-empty"><span aria-hidden="true">✓</span><div><b>All products have a price.</b><p>New zero-priced products will appear here for review.</p></div></div>
        @endif
    </section>
    <section class="admin-panel admin-legacy" id="customers"><p class="eyebrow">MIGRATED HISTORY</p><h2>Legacy orders &amp; customer records</h2><p>{{ number_format($legacyOrders->count()) }} historical orders and {{ number_format($legacyCustomersCount) }} customer records have been carried over in the protected archive. Historical records are preserved as imported; new order status controls apply to orders placed in this storefront.</p>@if($legacyOrders->isNotEmpty())<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Legacy order</th><th>Customer</th><th>Original status</th><th>Total</th><th>Placed</th></tr></thead><tbody>@foreach($legacyOrders as $legacyOrder)<tr><td>#{{ $legacyOrder->id ?? '' }}</td><td>{{ $legacyOrder->customer_name }}</td><td>{{ $legacyOrder->status ?? '—' }}</td><td>KES {{ number_format((float)($legacyOrder->total ?? 0)) }}</td><td>{{ $legacyOrder->created_at ?? '—' }}</td></tr>@endforeach</tbody></table></div>@endif</section>
    <div class="admin-columns"><div class="admin-panel" id="orders"><div class="admin-panel-head"><div><p class="eyebrow">FULFILMENT</p><h2>Recent orders</h2></div><a class="admin-text-link" href="{{ route('admin.orders') }}">All orders →</a></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Update</th></tr></thead><tbody>@forelse($orders as $order)<tr><td><b>{{ $order->reference }}</b><small>{{ $order->created_at->format('M j, Y') }}</small></td><td>{{ $order->customer_name }}<small>{{ $order->email }}</small></td><td>KES {{ number_format($order->total) }}</td><td><span class="status-chip">{{ str_replace('_',' ', $order->payment_status) }}</span><small>{{ strtoupper($order->payment_method) }}</small></td><td><form method="post" action="{{ route('admin.orders.update',$order->id) }}">@csrf @method('PATCH')<select name="order_status">@foreach(['pending','processing','shipped','completed','cancelled'] as $status)<option @selected($order->order_status === $status)>{{ $status }}</option>@endforeach</select><select name="payment_status">@foreach(['pending_payment','paid','escrow_held','refunded'] as $status)<option value="{{ $status }}" @selected($order->payment_status === $status)>{{ str_replace('_',' ',$status) }}</option>@endforeach</select><button class="text-link">Save</button></form></td></tr>@empty<tr><td colspan="5" class="table-empty">New orders will appear here.</td></tr>@endforelse</tbody></table></div>{{ $orders->links() }}</div>
    <aside class="admin-panel add-product"><p class="eyebrow">CATALOGUE</p><h2>Keep your catalogue current</h2><p>Update product details, stock, pricing, images, and search visibility from the catalogue workspace.</p><a class="admin-primary-action" href="{{ route('admin.products.create') }}">Add a product <span aria-hidden="true">→</span></a><a class="admin-secondary-action" href="{{ route('admin.products') }}">Manage catalogue</a></aside></div>
    <div class="admin-panel admin-products" id="products"><div class="admin-panel-head"><div><p class="eyebrow">INVENTORY</p><h2>Products</h2></div><span class="admin-table-result">{{ $products->total() }} products</span></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Product</th><th>Category</th><th>Brand</th><th>Price</th><th>Stock</th><th>Legacy image</th></tr></thead><tbody>@foreach($products as $product)<tr><td><b>{{ $product->name }}</b><small>{{ $product->slug }}</small></td><td>{{ $product->category }}</td><td>{{ $product->brand }}</td><td>KES {{ number_format($product->price) }}</td><td>{{ $product->stock }}</td><td>@if($product->legacy_image && !$product->image)<span class="missing-media">Needs media match</span>@else ✓ @endif</td></tr>@endforeach</tbody></table></div>{{ $products->links() }}</div>
</section>
<script>
(() => {
    const form = document.getElementById('zero-price-stock-form');
    if (!form) return;
    const selectAll = form.querySelector('#zero-price-select-all');
    const boxes = [...form.querySelectorAll('.zero-price-product-checkbox')];
    const count = form.querySelector('#zero-price-selected-count');
    const stock = form.querySelector('#zero-price-stock');
    const button = form.querySelector('#zero-price-update-button');
    const sync = () => {
        const selected = boxes.filter(box => box.checked).length;
        count.textContent = `${selected} selected`;
        button.disabled = selected === 0 || !stock.value;
        selectAll.checked = boxes.length > 0 && selected === boxes.length;
        selectAll.indeterminate = selected > 0 && selected < boxes.length;
    };
    selectAll.addEventListener('change', () => { boxes.forEach(box => { box.checked = selectAll.checked; }); sync(); });
    boxes.forEach(box => box.addEventListener('change', sync));
    stock.addEventListener('change', sync);
    form.addEventListener('submit', event => {
        if (!window.confirm(`Update stock status for ${boxes.filter(box => box.checked).length} zero-priced products?`)) event.preventDefault();
    });
    sync();
})();
</script>
<style>.admin-shell{max-width:1320px;margin:auto;padding:50px 30px 90px}.admin-heading{display:flex;justify-content:space-between;align-items:center}.admin-heading h1{font:600 38px var(--font-display);letter-spacing:-.06em;margin:0}.admin-heading p:not(.eyebrow){font-size:12px;color:var(--muted)}.admin-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:30px 0}.admin-stats>div,.admin-panel{border:1px solid var(--line);padding:20px;background:#fff}.admin-stats span,.admin-stats small{display:block;color:var(--muted);font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px}.admin-stats strong{font:700 25px var(--font-display);display:block;margin:8px 0 2px}.admin-columns{display:grid;grid-template-columns:1.6fr .8fr;gap:16px;align-items:start}.admin-panel-head{display:flex;justify-content:space-between;align-items:center}.admin-panel h2{font:600 17px var(--font-display);margin:0 0 18px}.admin-table-wrap{overflow:auto}.admin-table{width:100%;border-collapse:collapse;white-space:nowrap;font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px;color:#000}.admin-table th{text-align:left;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.1em;padding:10px;border-bottom:1px solid var(--line);font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px}.admin-table td{padding:12px 10px;border-bottom:1px solid var(--line)}.admin-table td small{display:block;color:var(--muted);font-size:12px;margin-top:5px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px}.admin-table select,.admin-panel-head input{border:1px solid var(--line);padding:8px;font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px;color:#000}.status-chip{background:#f5eddf;padding:5px 7px}.table-empty{text-align:center;color:var(--muted);padding:30px!important}.add-product form{display:flex;flex-direction:column;gap:11px}.add-product label{font-size:12px;color:var(--muted);font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px}.add-product input,.add-product select,.add-product textarea{width:100%;display:block;margin-top:5px;border:1px solid var(--line);padding:9px;font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px;color:#000}.admin-form-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}.admin-products{margin-top:16px}.missing-media{color:#9a5018}.admin-shell .pagination{display:flex;justify-content:center;gap:7px;list-style:none;padding:14px;font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px;color:#000}.admin-shell .pagination a{padding:4px 7px;background:var(--soft)}@media(max-width:900px){.admin-columns{grid-template-columns:1fr}.admin-products{margin-top:14px}}@media(max-width:640px){.admin-shell{padding:30px 15px 60px}.admin-heading h1{font-size:32px}.admin-stats{gap:7px}.admin-stats>div{padding:12px}.admin-stats span{font-size:12px;font-family:'Outfit Regular', 'Cairo Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight:400;line-height:30px;color:#000}.admin-stats strong{font-size:20px}}</style>
<style>
.admin-notice{margin:0 0 16px;padding:12px 15px;border:1px solid #cfe6dd;border-radius:8px;background:#f2faf6;color:#286e50;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.admin-notice-error{border-color:#efd4d4;background:#fff7f7;color:#a13d3d}
.admin-category-order{margin-bottom:16px;border-radius:10px}.admin-category-order .admin-panel-head{align-items:flex-end}.admin-category-order .admin-panel-head h2{margin:0}.admin-category-order .admin-panel-head>p{margin:0;color:#7e8983;font:400 13px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-category-help{margin:8px 0 17px;color:#7e8983;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-category-list{display:grid;border-top:1px solid #edf0ee}.admin-category-row{min-height:70px;display:grid;grid-template-columns:72px 46px minmax(0,1fr) auto;align-items:center;gap:14px;padding:10px 5px;border-bottom:1px solid #edf0ee;cursor:default}
.admin-category-position{display:grid;grid-template-columns:1fr;gap:3px;color:#829087}.admin-category-position small{font:500 12px/15px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.06em}.admin-category-position input{width:58px;height:34px;padding:0 7px;border:1px solid #dfe7e3;border-radius:6px;color:#202622;font:500 14px/20px 'Outfit Medium','Outfit','Cairo',sans-serif}.admin-category-thumb{width:46px;height:46px;display:grid;place-items:center;overflow:hidden;border-radius:7px;background:#f2f6f4;color:#0c6661}.admin-category-thumb img{width:100%;height:100%;object-fit:cover}.admin-category-name{min-width:0;display:grid;gap:3px}.admin-category-name strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#26322c;font:500 15px/21px 'Outfit Medium','Outfit','Cairo',sans-serif}.admin-category-name small{color:#89938d;font:400 12px/17px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.admin-category-row>a{color:#0c6661;font:500 13px/20px 'Outfit Medium','Outfit','Cairo',sans-serif}.admin-category-actions{display:flex;align-items:center;justify-content:space-between;gap:16px;padding-top:16px}.admin-category-actions>span{color:#89938d;font:400 12px/18px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.admin-category-actions .button{min-height:43px;padding:8px 15px;font-size:14px}
@media(max-width:520px){.admin-category-row{grid-template-columns:62px 40px minmax(0,1fr);gap:9px;padding:10px 0}.admin-category-position input{width:51px}.admin-category-thumb{width:40px;height:40px}.admin-category-row>a{grid-column:3;margin-top:-4px}.admin-category-actions{align-items:stretch;flex-direction:column}.admin-category-actions .button{width:100%}.admin-category-order .admin-panel-head{align-items:flex-start;flex-direction:column;gap:4px}}
</style>
@endsection

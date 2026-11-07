@extends('admin.layout')
@section('title', 'Customers | Admin')
@section('admin_page_title', 'Customers')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-directory-page">
    <div class="admin-page-heading"><div><p class="eyebrow">STORE MANAGEMENT</p><h1>Customers</h1><p>Customer accounts and their order history in one place.</p></div></div>
    <section class="catalogue-metrics directory-metrics"><article><span>Customer accounts</span><strong>{{ number_format($customerCount) }}</strong><small>Registered store customers</small></article><article class="is-featured"><span>Customers with orders</span><strong>{{ number_format($orderingCustomerCount) }}</strong><small>Unique customer emails</small></article><article class="is-warning"><span>Guest orders</span><strong>{{ number_format($guestOrderCount) }}</strong><small>Orders without a customer account</small></article></section>
    <section class="admin-panel directory-panel"><div class="directory-panel-heading"><div><p class="eyebrow">CUSTOMER ACCOUNTS</p><h2>Customer directory</h2></div><span class="admin-table-result">{{ number_format($customers->total()) }} results</span></div>
        <form class="directory-search" method="get" action="{{ route('admin.customers') }}"><label class="directory-search-grow"><span class="sr-only">Search customers</span><input type="search" name="q" value="{{ $search }}" placeholder="Search by name or email"></label><button class="admin-primary-action" type="submit">Search</button>@if($search !== '')<a class="catalogue-reset" href="{{ route('admin.customers') }}">Clear</a>@endif</form>
        <div class="admin-table-wrap directory-table-wrap"><table class="admin-table directory-table"><thead><tr><th>Customer</th><th>Joined</th><th>Orders</th><th>Most recent order</th><th>Account</th></tr></thead><tbody>@forelse($customers as $customer)<tr><td><div class="directory-customer"><span>{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span><div><strong>{{ $customer->name }}</strong><small>{{ $customer->email }}</small></div></div></td><td>{{ $customer->created_at?->format('j M Y') ?? '—' }}</td><td><strong>{{ number_format((int) $customer->orders_count) }}</strong></td><td>{{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('j M Y') : 'No orders yet' }}</td><td><span class="catalogue-stock {{ (int) $customer->orders_count ? 'in-stock' : '' }}">{{ (int) $customer->orders_count ? 'Returning shopper' : 'New customer' }}</span></td></tr>@empty<tr><td colspan="5" class="table-empty">No customers match this search.</td></tr>@endforelse</tbody></table></div>
        @if($customers->hasPages())<div class="catalogue-pagination">{{ $customers->links() }}</div>@endif
    </section>
</div>
@endsection

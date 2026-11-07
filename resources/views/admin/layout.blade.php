<!doctype html>
<html lang="en-KE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin | Crystal Car Audio')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="admin-app">
<div class="admin-frame" x-data="{ sidebarOpen: false }">
    <button class="admin-scrim" type="button" aria-label="Close navigation" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"></button>
    <aside class="admin-sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Admin navigation">
        <a class="admin-brand" href="{{ route('admin.home') }}"><span class="admin-brand-mark" aria-hidden="true"><svg viewBox="0 0 36 36" fill="none"><path d="M2 18h5l3-8 4 17 4-23 4 23 3-14 3 9h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><strong>Crystal<br>Car Audio</strong></span></a>
        <div class="admin-sidebar-caption">OVERVIEW</div>
        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.home') ? 'is-active' : '' }}" href="{{ route('admin.home') }}"><span class="admin-nav-icon">⌂</span>Dashboard</a>
            @can('view-admin-bi')
                <a class="{{ request()->routeIs('admin.bi') ? 'is-active' : '' }}" href="{{ route('admin.bi') }}"><span class="admin-nav-icon">◫</span>Business intelligence</a>
                <a href="{{ route('admin.bi', ['period' => '30d']) }}"><span class="admin-nav-icon">▤</span>Analytics &amp; reports</a>
            @endcan
        </nav>
        <div class="admin-sidebar-caption">STORE MANAGEMENT</div>
        <nav class="admin-nav">
            @can('manage-store-products')
                <a class="{{ request()->routeIs('admin.products*') ? 'is-active' : '' }}" href="{{ route('admin.products') }}"><span class="admin-nav-icon">▧</span>Products</a>
                <a class="{{ request()->routeIs('admin.categories*') ? 'is-active' : '' }}" href="{{ route('admin.categories') }}"><span class="admin-nav-icon">▦</span>Categories</a>
                <a class="{{ request()->routeIs('admin.exports*') ? 'is-active' : '' }}" href="{{ route('admin.exports') }}"><span class="admin-nav-icon">⇩</span>Product exports</a>
            @endcan
            @can('manage-store-products')<a class="{{ request()->routeIs('admin.brands*') ? 'is-active' : '' }}" href="{{ route('admin.brands') }}"><span class="admin-nav-icon">◇</span>Brands</a>@endcan
            @can('manage-store-products')<a class="{{ request()->routeIs('admin.seo*') ? 'is-active' : '' }}" href="{{ route('admin.seo') }}"><span class="admin-nav-icon">⌕</span>SEO management</a>@endcan
            @can('manage-store-inventory')<a class="{{ request()->routeIs('admin.inventory*') ? 'is-active' : '' }}" href="{{ route('admin.inventory') }}"><span class="admin-nav-icon">▤</span>Inventory</a>@endcan
            @can('manage-store-orders')
                <a class="{{ request()->routeIs('admin.orders*') ? 'is-active' : '' }}" href="{{ route('admin.orders') }}"><span class="admin-nav-icon">▣</span>Orders</a>
                <a class="{{ request()->routeIs('admin.customers*') ? 'is-active' : '' }}" href="{{ route('admin.customers') }}"><span class="admin-nav-icon">♙</span>Customers</a>
            @endcan
            @can('manage-installations')<a class="{{ request()->routeIs('admin.installations*') ? 'is-active' : '' }}" href="{{ route('admin.installations') }}"><span class="admin-nav-icon">◷</span>Installation bookings</a>@endcan
        </nav>
        @can('manage-store-users')
            <div class="admin-sidebar-caption">ADMINISTRATION</div>
            <nav class="admin-nav">
                <a class="{{ request()->routeIs('admin.users*') ? 'is-active' : '' }}" href="{{ route('admin.users') }}"><span class="admin-nav-icon">♙</span>Admin users &amp; roles</a>
                <a class="{{ request()->routeIs('admin.activity') ? 'is-active' : '' }}" href="{{ route('admin.activity') }}"><span class="admin-nav-icon">◷</span>Activity logs</a>
            </nav>
        @endcan
        <div class="admin-sidebar-bottom">
            <a href="{{ route('home') }}"><span class="admin-nav-icon">↗</span>View storefront</a>
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit"><span class="admin-nav-icon">⇥</span>Sign out</button></form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-title">
                <button class="admin-menu-toggle" type="button" aria-label="Open navigation" @click="sidebarOpen = true">☰</button>
                <div><span>Crystal Car Audio <b>/</b> Administration</span><strong>@yield('admin_page_title', 'Dashboard')</strong></div>
            </div>
            <div class="admin-topbar-actions">
                <form class="admin-global-search" method="get" action="{{ route('admin.home') }}"><span aria-hidden="true">⌕</span><input name="q" value="{{ request('q') }}" placeholder="Search products, orders, customers…" aria-label="Search products, orders, and customers"></form>
                <a class="admin-profile-link" href="{{ route('admin.profile') }}" aria-label="Admin profile and security settings">
                    <span class="admin-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="admin-profile-copy"><strong>{{ auth()->user()->name }}</strong><small>Admin profile &amp; security</small></span>
                    <span class="admin-profile-chevron" aria-hidden="true">⌄</span>
                </a>
            </div>
        </header>
        <main class="admin-content">
            @if(session('success'))<div class="admin-global-notice" role="status">{{ session('success') }}</div>@endif
            @yield('admin_content')
        </main>
    </div>
</div>
</body>
</html>

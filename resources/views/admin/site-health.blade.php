@extends('admin.layout')
@section('title', 'Site Health | Admin')
@section('admin_page_title', 'Site Health')
@section('admin_content')
<div class="admin-dashboard admin-shell admin-export-page admin-health-page">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">STORE OPERATIONS</p>
            <h1>Site Health</h1>
            <p>Review the status of key storefront and admin sections, then open the right page to update items that need attention.</p>
        </div>
        <a class="admin-primary-action" href="{{ route('admin.home') }}">Admin dashboard <span aria-hidden="true">→</span></a>
    </div>

    <div class="admin-export-stats" aria-label="Site health summary">
        <article><span class="admin-export-stat-icon is-teal" aria-hidden="true">✓</span><div><small>Sections healthy</small><strong>{{ $healthySections }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-amber" aria-hidden="true">!</span><div><small>Need attention</small><strong>{{ $attentionSections }}</strong></div></article>
        <article><span class="admin-export-stat-icon is-blue" aria-hidden="true">▧</span><div><small>Catalogue coverage</small><strong>{{ number_format($productCount) }} products</strong><small>{{ number_format($categoryCount) }} categories</small></div></article>
    </div>

    <section class="admin-health-grid" aria-label="Section health checks">
        @foreach($sections as $section)
            <article class="admin-health-card {{ $section['status'] === 'attention' ? 'is-attention' : 'is-healthy' }}">
                <div class="admin-health-card-heading">
                    <span class="admin-health-indicator" aria-hidden="true">{{ $section['status'] === 'attention' ? '!' : '✓' }}</span>
                    <span class="admin-health-status">{{ $section['status_label'] }}</span>
                </div>
                <h2>{{ $section['title'] }}</h2>
                <strong class="admin-health-count">{{ number_format($section['count']) }}</strong>
                <p>{{ $section['detail'] }}</p>
                @can($section['ability'])
                    <a class="admin-secondary-action" href="{{ route($section['route']).$section['fragment'] }}">{{ $section['action'] }} <span aria-hidden="true">→</span></a>
                @endcan
            </article>
        @endforeach
    </section>

    <section class="admin-health-database {{ $databaseConnected ? 'is-healthy' : 'is-attention' }}" aria-label="Database status">
        <span class="admin-health-indicator" aria-hidden="true">{{ $databaseConnected ? '✓' : '!' }}</span>
        <div><strong>Database connection</strong><p>{{ $databaseConnected ? 'The application can reach its database.' : 'The application could not confirm its database connection.' }}</p></div>
        <span class="admin-health-status">{{ $databaseConnected ? 'Connected' : 'Needs attention' }}</span>
    </section>

    <p class="admin-health-updated">Health counts refresh whenever this page loads.</p>
</div>
@endsection

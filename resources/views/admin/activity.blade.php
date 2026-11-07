@extends('admin.layout')
@section('title', 'Activity log | Crystal Car Audio Admin')
@section('admin_page_title', 'Activity log')
@section('admin_content')
<div class="admin-dashboard admin-module-page">
    <div class="admin-page-heading"><div><p class="admin-eyebrow">AUDIT TRAIL</p><h1>Changes across the store.</h1><p>Administrative actions with the responsible account and timestamp.</p></div></div>
    <section class="admin-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">RECENT ACTIVITY</p><h2>Activity log</h2></div><form class="admin-activity-search" method="get" action="{{ route('admin.activity') }}"><input name="q" value="{{ $search }}" placeholder="Search activity" aria-label="Search activity"><button type="submit">Search</button></form></div>
        @if($activities->isNotEmpty())<div class="admin-activity-list">@foreach($activities as $activity)<article class="admin-activity-row"><span class="admin-activity-dot" aria-hidden="true"></span><div><strong>{{ $activity->summary }}</strong><span>{{ $activity->user_name ?: 'Deleted account' }}@if($activity->user_email) · {{ $activity->user_email }}@endif</span></div><time datetime="{{ \Illuminate\Support\Carbon::parse($activity->created_at)->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($activity->created_at)->format('M j, Y · H:i') }}</time></article>@endforeach</div>{{ $activities->links() }}@else<div class="admin-bi-empty">No administrative activity has been recorded yet.</div>@endif
    </section>
</div>
@endsection

@extends('admin.layout')
@section('title', 'Installation bookings | Crystal Car Audio Admin')
@section('admin_page_title', 'Installation bookings')
@section('admin_content')
<div class="admin-dashboard admin-module-page">
    <div class="admin-page-heading"><div><p class="admin-eyebrow">INSTALLATION DESK</p><h1>Bookings and fitment.</h1><p>Schedule customer installations, assign technicians, and track completion.</p></div></div>
    <div class="admin-kpi-grid admin-install-kpis">
        <article class="admin-kpi-card"><span class="admin-kpi-label">Pending</span><strong>{{ number_format($pendingCount) }}</strong><small>Need confirmation</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-label">Confirmed</span><strong>{{ number_format($confirmedCount) }}</strong><small>Scheduled for fitment</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-label">Completed</span><strong>{{ number_format($completedCount) }}</strong><small>Bookings completed</small></article>
        <article class="admin-kpi-card"><span class="admin-kpi-label">Service charges</span><strong>KES {{ number_format($scheduledRevenue) }}</strong><small>Confirmed, in progress, and completed</small></article>
    </div>
    <div class="admin-booking-layout">
        <section class="admin-panel admin-booking-form-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">NEW BOOKING</p><h2>Create an installation</h2></div></div>
            <form class="admin-booking-form" method="post" action="{{ route('admin.installations.store') }}">@csrf
                <label>Customer name<input name="customer_name" value="{{ old('customer_name') }}" required maxlength="190"></label>
                <div class="admin-form-row"><label>Email address<input name="email" type="email" value="{{ old('email') }}" required maxlength="190"></label><label>Phone number<input name="phone" type="tel" value="{{ old('phone') }}" required maxlength="40"></label></div>
                <div class="admin-form-row"><label>Vehicle make<input name="vehicle_make" value="{{ old('vehicle_make') }}" required maxlength="100" placeholder="e.g. Toyota"></label><label>Model<input name="vehicle_model" value="{{ old('vehicle_model') }}" required maxlength="120" placeholder="e.g. Fielder"></label></div>
                <div class="admin-form-row"><label>Vehicle year<input name="vehicle_year" type="number" min="1950" max="{{ now()->year + 1 }}" value="{{ old('vehicle_year') }}"></label><label>Service<input name="service" value="{{ old('service') }}" required maxlength="190" placeholder="e.g. Head-unit installation"></label></div>
                <div class="admin-form-row"><label>Preferred date &amp; time<input name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}"></label><label>Technician<input name="technician" value="{{ old('technician') }}" maxlength="190"></label></div>
                <div class="admin-form-row"><label>Installation location<input name="location" value="{{ old('location') }}" maxlength="190"></label><label>Service charge (KES)<input name="service_charge" type="number" min="0" step="1" value="{{ old('service_charge', 0) }}" required></label></div>
                <label>Technician notes<textarea name="notes" rows="3" maxlength="5000">{{ old('notes') }}</textarea></label>
                <button class="admin-primary-action" type="submit">Create booking <span aria-hidden="true">→</span></button>
            </form>
        </section>
        <section class="admin-panel admin-booking-list-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">SCHEDULE</p><h2>Installation bookings</h2></div><form method="get" action="{{ route('admin.installations') }}"><select name="status" aria-label="Filter bookings by status" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(['pending','confirmed','in_progress','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($selectedStatus === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select></form></div>
            @if($bookings->isNotEmpty())<div class="admin-booking-list">@foreach($bookings as $booking)<article class="admin-booking-card"><div class="admin-booking-card-head"><div><strong>{{ $booking->customer_name }}</strong><span>{{ $booking->phone }} · {{ $booking->email }}</span></div><span class="admin-booking-status status-{{ $booking->status }}">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span></div><div class="admin-booking-details"><span><small>VEHICLE</small>{{ $booking->vehicle_year ? $booking->vehicle_year.' ' : '' }}{{ $booking->vehicle_make }} {{ $booking->vehicle_model }}</span><span><small>SERVICE</small>{{ $booking->service }}</span><span><small>DATE &amp; TIME</small>{{ $booking->scheduled_at ? \Illuminate\Support\Carbon::parse($booking->scheduled_at)->format('D, M j · g:i A') : 'To be scheduled' }}</span><span><small>LOCATION</small>{{ $booking->location ?: 'Not set' }} @if($booking->technician) · {{ $booking->technician }} @endif</span></div>
                <form class="admin-booking-update" method="post" action="{{ route('admin.installations.update', $booking->id) }}">@csrf @method('PATCH')<select name="status" aria-label="Booking status">@foreach(['pending','confirmed','in_progress','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($booking->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select><input name="scheduled_at" type="datetime-local" value="{{ $booking->scheduled_at ? \Illuminate\Support\Carbon::parse($booking->scheduled_at)->format('Y-m-d\TH:i') : '' }}" aria-label="Scheduled date and time"><input name="technician" value="{{ $booking->technician }}" placeholder="Assign technician" aria-label="Assigned technician"><input name="service_charge" type="number" min="0" step="1" value="{{ $booking->service_charge }}" aria-label="Service charge"><input name="notes" value="{{ $booking->notes }}" placeholder="Technician notes" aria-label="Technician notes"><button type="submit">Save</button></form>
            </article>@endforeach</div>{{ $bookings->links() }}@else<div class="admin-bi-empty">No bookings match this filter. Add an installation booking using the form.</div>@endif
        </section>
    </div>
</div>
@endsection

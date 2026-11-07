@extends('admin.layout')
@section('title', 'Admin users & roles | Crystal Car Audio Admin')
@section('admin_page_title', 'Admin users & roles')
@section('admin_content')
<div class="admin-dashboard admin-module-page">
    <div class="admin-page-heading"><div><p class="admin-eyebrow">ACCESS CONTROL</p><h1>People and permissions.</h1><p>Assign role-based access to existing customer accounts.</p></div></div>
    <div class="admin-role-notice"><strong>Configured administrator:</strong> {{ config('store.admin_email') }} always has super-administrator access. Other roles are enforced by server-side gates.</div>
    <section class="admin-panel"><div class="admin-panel-head"><div><p class="admin-eyebrow">ACCOUNT ACCESS</p><h2>Users &amp; roles</h2></div><span class="admin-table-result">{{ number_format($users->total()) }} accounts</span></div>
        <div class="admin-table-wrap"><table class="admin-table admin-users-table"><thead><tr><th>Account</th><th>Current access</th><th>Role permissions</th><th>Update access</th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td><td><span class="admin-role-chip {{ $user->admin_role ? 'is-admin' : '' }}">{{ $user->admin_role ? ($roles[$user->admin_role] ?? 'Administrator') : 'Customer' }}</span></td><td class="admin-role-permission">@switch($user->admin_role) @case('super_admin') All store settings and user roles @break @case('store_manager') Store operations and reporting @break @case('sales_administrator') Orders, customer records and reporting @break @case('inventory_manager') Product catalogue and stock @break @case('installation_coordinator') Installation bookings @break @case('marketing_manager') Product marketing and BI reports @break @case('bi_analyst') Read-only sales and BI reports @break @default Customer account @endswitch</td><td><form class="admin-role-form" method="post" action="{{ route('admin.users.role', $user) }}">@csrf @method('PATCH')<select name="admin_role" aria-label="Role for {{ $user->email }}"><option value="">Customer</option>@foreach($roles as $role => $label)<option value="{{ $role }}" @selected($user->admin_role === $role)>{{ $label }}</option>@endforeach</select><button type="submit">Save</button></form></td></tr>@endforeach</tbody></table></div>{{ $users->links() }}
    </section>
</div>
@endsection

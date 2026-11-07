@extends('admin.layout')
@section('title', 'Admin profile | Crystal Car Audio')
@section('admin_page_title', 'Admin profile')
@section('admin_content')
<section class="admin-dashboard admin-shell admin-profile-page">
    <div class="admin-page-heading"><div><p class="admin-eyebrow">ACCOUNT SETTINGS</p><h1>Your admin profile</h1><p>Manage your account details and sign-in security.</p></div></div>

    <div class="admin-profile-grid">
        <section class="admin-panel admin-profile-summary">
            <span class="admin-profile-large-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div><p class="admin-eyebrow">SIGNED IN AS</p><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><span class="admin-role-chip is-admin">{{ $user->admin_role ? str_replace('_', ' ', ucfirst($user->admin_role)) : 'Store administrator' }}</span></div>
        </section>

        <section class="admin-panel admin-security-panel" aria-labelledby="email-2fa-title">
            <div class="admin-panel-head"><div><p class="admin-eyebrow">SIGN-IN SECURITY</p><h2 id="email-2fa-title">Email two-factor authentication</h2></div><span class="admin-security-state {{ $user->admin_email_2fa_enabled ? 'is-enabled' : '' }}">{{ $user->admin_email_2fa_enabled ? 'On' : 'Off' }}</span></div>
            <p class="admin-security-description">Add a verification step when you sign in. After your password, we’ll email a one-time code to <strong>{{ $user->email }}</strong>. Codes expire after 10 minutes.</p>
            @unless($emailDeliveryReady)<div class="admin-security-mail-notice" role="status">Email delivery is not configured for this environment. Set up a delivery mailer before turning this on so you can receive your sign-in code.</div>@endunless

            <form method="post" action="{{ route('admin.profile.security') }}" class="admin-security-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="admin_email_2fa_enabled" value="0">
                <label class="admin-security-toggle" for="admin-email-2fa">
                    <span><strong>Require an email code at sign-in</strong><small>This setting is {{ $user->admin_email_2fa_enabled ? 'currently enabled' : 'off by default' }} for your account.</small></span>
                    <input id="admin-email-2fa" type="checkbox" name="admin_email_2fa_enabled" value="1" @checked($user->admin_email_2fa_enabled)>
                    <span class="admin-toggle-track" aria-hidden="true"><i></i></span>
                </label>
                @error('admin_email_2fa_enabled')<p class="admin-field-error" role="alert">{{ $message }}</p>@enderror
                <div class="admin-security-actions"><p>Your sign-in code is single-use and is never stored as plain text.</p><button class="admin-primary-action" type="submit">Save security setting <span aria-hidden="true">→</span></button></div>
            </form>
        </section>
    </div>
</section>
@endsection

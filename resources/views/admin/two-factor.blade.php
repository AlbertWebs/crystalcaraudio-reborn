@extends('store.layout')
@section('title', 'Verify sign-in | Crystal Car Audio Admin')
@section('content')
<section class="admin-two-factor-page">
    <div class="admin-two-factor-card">
        <div class="admin-two-factor-mark" aria-hidden="true">✳</div>
        <p class="eyebrow">ADMIN SIGN-IN</p>
        <h1>Check your email</h1>
        <p class="admin-two-factor-intro">We sent a six-digit sign-in code to <strong>{{ $maskedEmail }}</strong>. Enter it below to continue to the admin panel.</p>
        @if(session('status'))<p class="admin-two-factor-status" role="status">{{ session('status') }}</p>@endif
        <form method="post" action="{{ route('admin.two-factor.verify') }}" class="admin-two-factor-form">
            @csrf
            <label for="admin-login-code">Verification code</label>
            <input id="admin-login-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="000000" required autofocus>
            @error('code')<small role="alert">{{ $message }}</small>@enderror
            <p class="admin-two-factor-expiry">This code expires in 10 minutes.</p>
            <button class="button button-dark full-button" type="submit">Verify and continue <span aria-hidden="true">→</span></button>
        </form>
        <form method="post" action="{{ route('admin.two-factor.resend') }}" class="admin-two-factor-resend">@csrf<button type="submit">Send a new code</button></form>
        <a class="admin-two-factor-back" href="{{ route('admin.login') }}">Back to admin sign-in</a>
    </div>
</section>
<style>
    .admin-two-factor-page{min-height:62vh;display:grid;place-items:center;padding:44px 18px;background:#f4f7f5}
    .admin-two-factor-card{width:min(100%,460px);padding:34px;border:1px solid #e3eae6;border-radius:18px;background:#fff;box-shadow:0 20px 55px #0a484b12}
    .admin-two-factor-mark{width:46px;height:46px;display:grid;place-items:center;margin-bottom:20px;border-radius:14px;background:#e8f5f2;color:#08746d;font-size:22px}
    .admin-two-factor-card>.eyebrow{margin:0 0 7px;color:#08746d;font-size:12px;font-weight:600;letter-spacing:.12em}
    .admin-two-factor-card h1{margin:0;color:#17231f;font:600 30px/36px 'Outfit',sans-serif;letter-spacing:-.04em}
    .admin-two-factor-intro{margin:10px 0 0;color:#66736d;font:400 14px/22px 'Outfit',sans-serif}.admin-two-factor-intro strong{color:#273831;font-weight:500}
    .admin-two-factor-form{display:grid;gap:9px;margin-top:24px}.admin-two-factor-form label{color:#394a43;font:500 13px/19px 'Outfit',sans-serif}
    .admin-two-factor-form input{width:100%;height:56px;padding:0 14px;border:1px solid #dce6e1;border-radius:9px;color:#183c37;text-align:center;font:600 22px/28px 'Outfit',sans-serif;letter-spacing:.28em}
    .admin-two-factor-form input:focus{outline:3px solid #08746d24;border-color:#08746d}.admin-two-factor-form small{color:#b14037;font:400 12px/18px 'Outfit',sans-serif}
    .admin-two-factor-expiry{margin:0 0 5px;color:#84918a;font:400 12px/18px 'Outfit',sans-serif}
    .admin-two-factor-form .button{min-height:46px;display:flex;align-items:center;justify-content:center;gap:10px;border-radius:8px;font:500 14px/20px 'Outfit',sans-serif}
    .admin-two-factor-status{margin:15px 0 0;padding:10px 12px;border:1px solid #d5ece3;border-radius:8px;background:#f1faf6;color:#287451;font:400 13px/19px 'Outfit',sans-serif}
    .admin-two-factor-resend{margin-top:17px;text-align:center}.admin-two-factor-resend button,.admin-two-factor-back{border:0;background:transparent;color:#08746d;font:500 13px/20px 'Outfit',sans-serif;text-decoration:underline;text-underline-offset:3px;cursor:pointer}
    .admin-two-factor-back{display:block;margin-top:13px;text-align:center;font-weight:400}
    @media(max-width:480px){.admin-two-factor-card{padding:25px 20px;border-radius:14px}.admin-two-factor-card h1{font-size:26px;line-height:32px}}
</style>
@endsection

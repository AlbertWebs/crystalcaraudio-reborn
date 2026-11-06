@extends('store.layout')
@section('title','Set up your password | Crystal Car Audio')
@section('content')
<main class="password-flow-page"><section class="password-flow-card">
    <p class="eyebrow">YOUR CRYSTAL ACCOUNT</p><h1>Set your password.</h1>
    <p class="password-flow-intro">Enter the email you used at checkout. We’ll send a secure link to set or reset your password.</p>
    @if(session('status'))<p class="password-flow-status" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<p class="password-flow-error" role="alert">{{ $errors->first() }}</p>@endif
    <form method="post" action="{{ route('password.email') }}">@csrf
        <label for="reset-email">Email address</label><input id="reset-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="you@example.com">
        <button class="button button-dark" type="submit">Email me a secure link <span aria-hidden="true">→</span></button>
    </form>
    <a class="password-flow-back" href="{{ route('login') }}">← Back to sign in</a>
</section></main>
<style>
.password-flow-page{min-height:490px;padding:54px 18px 72px;display:grid;place-items:center;background:linear-gradient(145deg,#f4f8f6,#fff 68%)}.password-flow-card{width:min(100%,490px);padding:38px;background:#fff;border:1px solid #e5ece9;border-radius:14px;box-shadow:0 16px 45px rgba(18,52,47,.07)}.password-flow-card .eyebrow{margin:0 0 8px}.password-flow-card h1{margin:0;color:#202622;font:600 34px/1.15 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.04em}.password-flow-intro{margin:12px 0 23px;color:#888;font:400 16px/24px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.password-flow-card form{display:grid;gap:10px}.password-flow-card label{color:#343a39;font:500 15px/22px 'Outfit Medium','Outfit','Cairo',sans-serif}.password-flow-card input{height:49px;padding:0 14px;border:1px solid #dce5e2;border-radius:7px;font:400 16px/24px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.password-flow-card input:focus{outline:2px solid #0c666133;border-color:#0c6661}.password-flow-card .button{min-height:48px;margin-top:9px;justify-content:center;font-size:14px}.password-flow-back{display:block;margin-top:22px;text-align:center;color:#0c6661;font:500 14px/22px 'Outfit Medium','Outfit','Cairo',sans-serif}.password-flow-status,.password-flow-error{padding:11px 13px;border-radius:7px;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.password-flow-status{background:#f1faf5;color:#267054}.password-flow-error{background:#fff6f6;color:#9d3535}@media(max-width:520px){.password-flow-card{padding:28px 22px}.password-flow-card h1{font-size:30px}}
</style>
@endsection

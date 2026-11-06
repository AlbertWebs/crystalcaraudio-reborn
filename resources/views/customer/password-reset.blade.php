@extends('store.layout')
@section('title','Choose a password | Crystal Car Audio')
@section('content')
<main class="password-flow-page"><section class="password-flow-card">
    <p class="eyebrow">YOUR CRYSTAL ACCOUNT</p><h1>Choose a password.</h1>
    <p class="password-flow-intro">Set a password to finish activating your account and access your orders.</p>
    @if($errors->any())<p class="password-flow-error" role="alert">{{ $errors->first() }}</p>@endif
    <form method="post" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="reset-email">Email address</label><input id="reset-email" type="email" name="email" value="{{ old('email',$email) }}" autocomplete="email" required>
        <label for="new-password">New password</label><input id="new-password" type="password" name="password" autocomplete="new-password" minlength="10" required placeholder="At least 10 characters">
        <label for="confirm-password">Confirm password</label><input id="confirm-password" type="password" name="password_confirmation" autocomplete="new-password" minlength="10" required placeholder="Enter it again">
        <button class="button button-dark" type="submit">Save password <span aria-hidden="true">→</span></button>
    </form>
    <a class="password-flow-back" href="{{ route('login') }}">← Back to sign in</a>
</section></main>
@include('customer.forgot-password-style')
@endsection

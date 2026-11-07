@extends('store.layout')
@section('title', 'Admin sign in | Crystal Car Audio')
@section('content')
<div class="admin-auth-page">
    <div class="admin-auth-card">
        <aside class="admin-auth-story" aria-label="Crystal Car Audio administration">
            <a class="admin-auth-brand" href="{{ route('home') }}" aria-label="Crystal Car Audio home">
                <img src="{{ \App\Support\MediaUrl::url('uploads/logo/Crystal%20Logo-06.png') }}" alt="Crystal Car Audio">
            </a>
            <span class="admin-auth-kicker">CRYSTAL CAR AUDIO · ADMIN</span>
            <h1>Your store.<br>Your sound.</h1>
            <p>Manage your catalogue, orders and customer experience in one place.</p>
            <div class="admin-auth-pulse" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </aside>

        <section class="admin-auth-form-panel" aria-labelledby="admin-login-heading">
            <div class="admin-auth-heading">
                <p class="eyebrow">SECURE ADMIN ACCESS</p>
                <h2 id="admin-login-heading">Welcome back.</h2>
                <p>Sign in to continue to your dashboard.</p>
            </div>

            @if($errors->any())
                <div class="admin-auth-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('admin.login.post') }}" class="admin-auth-form" x-data="{ showPassword: false }">
                @csrf
                <label for="admin-login-email">Email address
                    <input id="admin-login-email" required type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="you@example.com" @error('email') aria-invalid="true" @enderror autofocus>
                    @error('email')<small class="admin-auth-field-error">{{ $message }}</small>@enderror
                </label>
                <label for="admin-login-password">Password
                    <span class="admin-auth-password-wrap">
                        <input id="admin-login-password" required :type="showPassword ? 'text' : 'password'" name="password" autocomplete="current-password" placeholder="Enter your password">
                        <button class="admin-auth-password-toggle" type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword.toString()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.7"/></svg></button>
                    </span>
                </label>
                <label class="admin-auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Keep me signed in</span></label>
                <button class="button button-dark admin-auth-submit" type="submit">Sign in to dashboard <span aria-hidden="true">→</span></button>
            </form>
            <p class="admin-auth-help">Need access help? <a href="mailto:info@crystalcaraudio.com">Contact Crystal support</a></p>
            <a class="admin-auth-store-link" href="{{ route('home') }}">← Return to the store</a>
        </section>
    </div>
</div>

<style>
.admin-auth-page{min-height:calc(100vh - 120px);padding:46px 22px 68px;display:grid;place-items:center;background:radial-gradient(ellipse at 17% 12%,#eaf5f1 0,transparent 38%),#f7f9f7;font-family:'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-auth-card{width:min(100%,1020px);min-height:560px;display:grid;grid-template-columns:.82fr 1.18fr;overflow:hidden;border:1px solid #e2ebe6;border-radius:18px;background:#fff;box-shadow:0 22px 70px rgba(17,55,45,.1)}
.admin-auth-story{position:relative;isolation:isolate;overflow:hidden;display:flex;flex-direction:column;align-items:flex-start;padding:38px 40px;background:linear-gradient(145deg,#075d58 0%,#084743 62%,#092f30 100%);color:#fff}
.admin-auth-brand{width:146px;height:62px;display:grid;place-items:center}.admin-auth-brand img{width:100%;height:100%;object-fit:contain;filter:brightness(0) invert(1)}
.admin-auth-kicker{margin-top:63px;color:#b4dfd5;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.15em}
.admin-auth-story h1{margin:14px 0 0;font:600 clamp(34px,4vw,44px)/1.08 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.04em}
.admin-auth-story>p{max-width:300px;margin:14px 0 0;color:#d0e2dd;font:400 16px/24px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-auth-pulse{position:absolute;z-index:-1;left:0;right:0;bottom:0;height:112px;display:flex;align-items:flex-end;justify-content:space-around;gap:5px;opacity:.2;transform:skewY(-8deg)}
.admin-auth-pulse span{width:3px;height:var(--bar,34px);border-radius:5px 5px 0 0;background:linear-gradient(#9ad9cd,#fff)}.admin-auth-pulse span:nth-child(5n+1){--bar:24px}.admin-auth-pulse span:nth-child(5n+2){--bar:52px}.admin-auth-pulse span:nth-child(5n+3){--bar:38px}.admin-auth-pulse span:nth-child(5n+4){--bar:68px}.admin-auth-pulse span:nth-child(7n){--bar:84px}
.admin-auth-form-panel{padding:48px clamp(30px,5vw,62px) 38px;display:flex;flex-direction:column;justify-content:center}
.admin-auth-heading .eyebrow{margin:0 0 8px;color:#08766e;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.14em}
.admin-auth-heading h2{margin:0;color:#222b26;font:600 32px/39px 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.035em}
.admin-auth-heading>p:last-child{margin:8px 0 0;color:#8c9690;font:400 15px/22px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-auth-error{margin-top:18px;padding:11px 13px;border:1px solid #efd4d4;border-radius:7px;background:#fff7f7;color:#a13d3d;font:400 13px/19px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-auth-form{display:grid;gap:17px;margin-top:28px}
.admin-auth-form>label{display:block;color:#38423c;font:500 14px/20px 'Outfit Medium','Outfit','Cairo',sans-serif}
.admin-auth-form input:not([type=checkbox]){width:100%;height:48px;margin-top:7px;padding:0 14px;border:1px solid #dfe7e3;border-radius:7px;background:#fff;color:#202622;font:400 14px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;outline:none;transition:border-color .18s,box-shadow .18s}
.admin-auth-form input::placeholder{color:#a1aaa5}.admin-auth-form input:focus{border-color:#0c766e;box-shadow:0 0 0 3px rgba(12,102,97,.1)}.admin-auth-form input[aria-invalid=true]{border-color:#bd4b4b}
.admin-auth-password-wrap{position:relative;display:block}.admin-auth-password-wrap input{padding-right:50px!important}
.admin-auth-password-toggle{position:absolute;right:8px;top:12px;width:34px;height:34px;display:grid;place-items:center;border:0;border-radius:6px;background:transparent;color:#718079;cursor:pointer}.admin-auth-password-toggle:hover{background:#eef6f3;color:#0c6661}.admin-auth-password-toggle svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.admin-auth-field-error{display:block;margin-top:5px;color:#b34444;font:400 12px/18px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.admin-auth-remember{display:flex!important;align-items:center;gap:9px;margin-top:-2px;color:#78847d!important;font:400 13px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif!important;cursor:pointer}.admin-auth-remember input{width:16px;height:16px;margin:0;accent-color:#0c6661}
.admin-auth-submit{width:100%;min-height:50px;margin-top:2px;border-radius:7px;justify-content:center;font:500 15px/22px 'Outfit Medium','Outfit','Cairo',sans-serif!important}.admin-auth-submit span{margin-left:5px;font-size:19px;transition:transform .18s}.admin-auth-submit:hover span{transform:translateX(3px)}
.admin-auth-help{margin:22px 0 0;text-align:center;color:#8c9690;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.admin-auth-help a{color:#0c6661;font-weight:500;text-decoration:underline;text-underline-offset:3px}
.admin-auth-store-link{align-self:center;margin-top:19px;color:#64746c;font:400 13px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;text-decoration:none}.admin-auth-store-link:hover{color:#0c6661}
@media(max-width:760px){.admin-auth-page{min-height:auto;padding:28px 15px 52px}.admin-auth-card{max-width:540px;min-height:0;grid-template-columns:1fr;border-radius:14px}.admin-auth-story{min-height:228px;padding:24px 26px}.admin-auth-brand{width:132px;height:54px}.admin-auth-kicker{margin-top:25px}.admin-auth-story h1{font-size:31px}.admin-auth-story>p{max-width:350px;margin-top:8px;font-size:14px;line-height:21px}.admin-auth-pulse{height:45px;opacity:.16}.admin-auth-form-panel{padding:31px 26px 28px}.admin-auth-heading h2{font-size:27px;line-height:34px}.admin-auth-form{margin-top:22px;gap:14px}}
@media(max-width:430px){.admin-auth-page{padding:18px 11px 38px}.admin-auth-story{min-height:208px;padding:20px}.admin-auth-kicker{margin-top:21px}.admin-auth-story h1{font-size:28px}.admin-auth-form-panel{padding:27px 20px 24px}}
</style>
@endsection

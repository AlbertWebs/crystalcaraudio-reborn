@extends('store.layout')
@section('title','Sign in | Crystal Car Audio')
@section('content')
<main class="register-page">
    <div class="register-card">
        <aside class="register-story">
            <a class="register-brand" href="{{ route('home') }}" aria-label="Crystal Car Audio home"><img src="{{ asset('uploads/logo/Crystal%20Logo-06.png') }}" alt="Crystal Car Audio"></a>
            <span class="register-story-label">YOUR CRYSTAL ACCOUNT</span>
            <h1>Your drive.<br>Your sound.</h1>
            <p>Pick up where you left off and keep your favourite upgrades close.</p>
            <ul><li><span aria-hidden="true">✓</span> Check your latest orders</li><li><span aria-hidden="true">✓</span> Find your saved products</li><li><span aria-hidden="true">✓</span> Keep your delivery details ready</li></ul>
            <div class="register-wave" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        </aside>
        <section class="register-form-panel" aria-labelledby="login-heading">
            <div class="register-heading"><p class="eyebrow">WELCOME BACK</p><h2 id="login-heading">Sign in to your account</h2><p>Your orders and saved products are waiting.</p></div>
            @if(session('status'))<p class="login-status" role="status">{{ session('status') }}</p>@endif
            @if($errors->any())<div class="register-error" role="alert">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ route('login.post') }}" class="register-form">@csrf
                <label for="login-email">Email address<input id="login-email" required type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="you@example.com" @error('email') aria-invalid="true" @enderror>@error('email')<small class="register-field-error">{{ $message }}</small>@enderror</label>
                <label for="login-password">Password<input id="login-password" required type="password" name="password" autocomplete="current-password" placeholder="Enter your password"></label>
                <div class="login-form-options"><label><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Keep me signed in</span></label><a href="{{ route('password.request') }}">Forgot password?</a></div>
                <button class="button button-dark register-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
            </form>
            <p class="register-signin">New to Crystal? <a href="{{ route('register') }}">Create an account</a></p>
        </section>
    </div>
</main>
<style>
.login-status{margin:16px 0 0;padding:11px 13px;border:1px solid #cfe6dd;border-radius:7px;background:#f2faf6;color:#286e50;font:400 13px/19px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.login-form-options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 2px}
.login-form-options label{display:flex;align-items:center;gap:8px;color:#78847d;font:400 13px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;cursor:pointer}
.login-form-options input{width:16px;height:16px;margin:0;accent-color:#0c6661}
.login-form-options>a{color:#0c6661;font:500 13px/20px 'Outfit Medium','Outfit','Cairo',sans-serif;text-decoration:underline;text-underline-offset:3px;white-space:nowrap}
/* Match the split account card, logo treatment, spacing, and Outfit type used on registration. */
.register-page{min-height:calc(100vh - 116px);padding:48px 22px 70px;display:grid;place-items:center;background:radial-gradient(ellipse at 18% 12%,#eaf5f1 0,transparent 38%),#f7f9f7;font-family:'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.register-card{width:min(100%,1040px);min-height:620px;display:grid;grid-template-columns:.84fr 1.16fr;overflow:hidden;border:1px solid #e3ebe6;border-radius:18px;background:#fff;box-shadow:0 22px 70px rgba(17,55,45,.1)}
.register-story{position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:flex-start;padding:38px 40px;background:linear-gradient(145deg,#075d58 0%,#084743 62%,#092f30 100%);color:#fff}
.register-brand{width:150px;height:62px;display:grid;place-items:center;background:transparent}.register-brand img{width:100%;height:100%;object-fit:contain;filter:brightness(0) invert(1)}
.register-story-label{margin-top:53px;color:#b4dfd5;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.16em}
.register-story h1{margin:13px 0 0;font:600 clamp(32px,4vw,43px)/1.08 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.04em}
.register-story>p{max-width:270px;margin:13px 0 0;color:#d0e2dd;font:400 16px/24px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.register-story ul{display:grid;gap:15px;margin:32px 0 0;padding:0;list-style:none;color:#f3f8f5;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.register-story li{display:flex;align-items:center;gap:11px}.register-story li span{width:22px;height:22px;display:grid;place-items:center;border-radius:50%;background:#ffffff19;color:#a7e5d8;font-size:12px}
.register-wave{position:absolute;left:0;right:0;bottom:0;height:102px;display:flex;align-items:center;justify-content:space-around;gap:5px;opacity:.24;transform:skewY(-8deg)}
.register-wave i{width:3px;height:var(--bar-height,34px);border-radius:5px;background:linear-gradient(#9ad9cd,#fff)}.register-wave i:nth-child(5n+1){--bar-height:24px}.register-wave i:nth-child(5n+2){--bar-height:52px}.register-wave i:nth-child(5n+3){--bar-height:38px}.register-wave i:nth-child(5n+4){--bar-height:68px}.register-wave i:nth-child(7n){--bar-height:84px}
.register-form-panel{padding:46px clamp(28px,5vw,58px) 36px;display:flex;flex-direction:column;justify-content:center}
.register-heading .eyebrow{margin:0 0 8px;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.14em}
.register-heading h2{margin:0;color:#222b26;font:600 30px/38px 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.035em}
.register-heading>p:last-child{margin:8px 0 0;color:#8c9690;font:400 15px/22px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.register-error{margin-top:17px;padding:11px 13px;border:1px solid #efd4d4;border-radius:7px;background:#fff7f7;color:#a13d3d;font:400 13px/19px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.register-form{display:grid;gap:14px;margin-top:23px}
.register-form label{display:block;color:#38423c;font:500 14px/20px 'Outfit Medium','Outfit','Cairo',sans-serif}
.register-form input:not([type=checkbox]){width:100%;height:46px;margin-top:6px;padding:0 13px;border:1px solid #dfe7e3;border-radius:7px;background:#fff;color:#202622;font:400 14px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;outline:none;transition:border-color .18s,box-shadow .18s}
.register-form input::placeholder{color:#a1aaa5}.register-form input:focus{border-color:#0c766e;box-shadow:0 0 0 3px rgba(12,102,97,.1)}.register-form input[aria-invalid=true]{border-color:#bd4b4b}
.register-submit{width:100%;min-height:48px;margin-top:2px;border-radius:7px;justify-content:center;font:500 15px/22px 'Outfit Medium','Outfit','Cairo',sans-serif!important}.register-submit span{margin-left:4px;font-size:19px;transition:transform .18s}.register-submit:hover span{transform:translateX(3px)}
.register-signin{margin:20px 0 0;text-align:center;color:#8c9690;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.register-signin a{color:#0c6661;font-weight:500;text-decoration:underline;text-underline-offset:3px}
@media(max-width:760px){.register-page{min-height:auto;padding:27px 15px 52px}.register-card{max-width:520px;min-height:0;grid-template-columns:1fr;border-radius:14px}.register-story{min-height:235px;padding:24px 25px}.register-brand{width:130px;height:52px}.register-story-label{margin-top:24px}.register-story h1{font-size:30px}.register-story>p{max-width:330px;margin-top:8px;font-size:14px;line-height:21px}.register-story ul{display:none}.register-wave{height:40px;opacity:.17}.register-form-panel{padding:30px 25px 26px}.register-heading h2{font-size:26px;line-height:33px}.register-form{margin-top:19px;gap:12px}}
@media(max-width:430px){.register-page{padding:18px 11px 38px}.register-story{min-height:208px;padding:19px 20px}.register-story-label{margin-top:20px}.register-story h1{font-size:27px}.register-form-panel{padding:26px 19px 22px}}
</style>
@endsection

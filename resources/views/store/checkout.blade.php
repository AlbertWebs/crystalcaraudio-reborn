@extends('store.layout')
@section('title', 'Checkout | Crystal Car Audio')
@section('content')
@php
    $checkoutStep = $errors->has('payment_method') ? 1 : 0;
@endphp
<section class="page-hero compact checkout-hero">
    <p class="eyebrow">ALMOST YOURS</p>
    <h1>Checkout.</h1>
    <p>Your details stay private and protected.</p>
</section>
<nav class="checkout-progress" aria-label="Checkout steps">
    <button class="is-current" type="button" data-step-indicator="0" aria-current="step"><i>01</i><span>Contact &amp; delivery</span></button><b aria-hidden="true"></b>
    <button type="button" data-step-indicator="1" disabled><i>02</i><span>Payment</span></button><b aria-hidden="true"></b>
    <button type="button" data-step-indicator="2" disabled><i>03</i><span>Confirmation</span></button>
</nav>
<section class="section cart-layout checkout-layout">
    <div class="checkout-main">
        <form id="checkout-order-form" class="checkout-form" method="post" action="{{ route('checkout.order') }}" data-checkout-wizard data-current-step="{{ $checkoutStep }}">
            @csrf
            <div class="checkout-wizard-card">
                <section class="checkout-step-panel" data-step-panel="0" aria-labelledby="contact-heading" @if($checkoutStep !== 0) hidden @endif>
                    <div class="checkout-section-heading"><span>01</span><div><h2 id="contact-heading">Your contact &amp; delivery details</h2><p>Tell us how to reach you and where to bring your order.</p></div></div>
                    @if($errors->hasAny(['name', 'email', 'phone', 'address']))<div class="checkout-validation" role="alert">Please check your contact and delivery details, then try again.</div>@endif
                    <div class="form-grid">
                        <label for="checkout-name">Full name<input id="checkout-name" required name="name" value="{{ old('name', $checkoutDetails['name'] ?? '') }}" autocomplete="name" placeholder="Your full name" @error('name') aria-invalid="true" @enderror></label>
                        <label for="checkout-email">Email address<input id="checkout-email" required type="email" name="email" value="{{ old('email', $checkoutDetails['email'] ?? '') }}" autocomplete="email" placeholder="you@example.com" @error('email') aria-invalid="true" @enderror></label>
                        <label for="checkout-phone">Phone number<input id="checkout-phone" required type="tel" name="phone" value="{{ old('phone', $checkoutDetails['phone'] ?? '') }}" placeholder="+254 7XX XXX XXX" autocomplete="tel" @error('phone') aria-invalid="true" @enderror><small>We’ll use this number to coordinate delivery.</small></label>
                        <label for="checkout-address">Delivery address<input id="checkout-address" required name="address" value="{{ old('address', $checkoutDetails['address'] ?? '') }}" placeholder="Street, estate, town" autocomplete="street-address" @error('address') aria-invalid="true" @enderror></label>
                    </div>
                    <label class="checkout-create-account" for="checkout-create-account"><input id="checkout-create-account" type="checkbox" name="create_account" value="1" @checked(old('create_account', $checkoutDetails['create_account'] ?? false))><span><strong>Create an account</strong><small>We’ll email you a secure link to set your password and view your orders.</small></span></label>
                    <div class="checkout-step-actions checkout-actions-end"><button class="button button-dark checkout-next" type="button" data-step-next>Continue to payment <span aria-hidden="true">→</span></button></div>
                </section>

                <section class="checkout-step-panel" data-step-panel="1" aria-labelledby="payment-heading" @if($checkoutStep !== 1) hidden @endif>
                    <div class="checkout-section-heading"><span>02</span><div><h2 id="payment-heading">Choose how to pay</h2><p>Select your preferred payment method.</p></div></div>
                    @error('payment_method')<p class="checkout-field-error" role="alert">{{ $message }}</p>@enderror
                    <div class="checkout-payment-options">
                        <label class="payment-choice" for="payment-cod"><input id="payment-cod" type="radio" name="payment_method" value="cod" required @checked(old('payment_method', $checkoutDetails['payment_method'] ?? 'cod') === 'cod')><span class="payment-choice-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h3"/><circle cx="17" cy="14.5" r="1.5"/></svg></span><span class="payment-choice-copy"><b>Cash on Delivery</b><small>Pay when your order arrives.</small></span><span class="payment-choice-indicator" aria-hidden="true"></span></label>
                        <label class="payment-choice" for="payment-econfirm"><input id="payment-econfirm" type="radio" name="payment_method" value="econfirm" @checked(old('payment_method', $checkoutDetails['payment_method'] ?? '') === 'econfirm')><span class="payment-choice-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span><span class="payment-choice-copy"><b>eConfirm Escrow</b><small>Protected M-Pesa payment held securely in escrow.</small></span><span class="payment-choice-indicator" aria-hidden="true"></span></label>
                    </div>
                    <div class="checkout-step-actions"><button class="button button-light checkout-back" type="button" data-step-back>← Back</button><button class="button button-dark checkout-next" type="button" data-step-next>Review order <span aria-hidden="true">→</span></button></div>
                </section>

                <section class="checkout-step-panel" data-step-panel="2" aria-labelledby="confirmation-heading" @if($checkoutStep !== 2) hidden @endif>
                    <div class="checkout-section-heading"><span>03</span><div><h2 id="confirmation-heading">Confirm your order</h2><p>Check your details before placing the order.</p></div></div>
                    <div class="checkout-review-card"><div><span>Contact</span><button type="button" data-go-step="0">Edit</button></div><p data-review-contact>Enter your contact details</p><div><span>Delivery</span><button type="button" data-go-step="0">Edit</button></div><p data-review-delivery>Enter your delivery information</p><div><span>Payment</span><button type="button" data-go-step="1">Edit</button></div><p data-review-payment>Select a payment method</p></div>
                    <p class="checkout-privacy-note"><span aria-hidden="true">✓</span> Your details are used to process and deliver this order.</p>
                    <div class="checkout-step-actions"><button class="button button-light checkout-back" type="button" data-step-back>← Back</button></div>
                </section>
            </div>
        </form>
    </div>
    <aside class="order-summary checkout-order-summary" aria-label="Order summary">
        <h2>Your order <span>{{ $cart->sum('quantity') }} {{ $cart->sum('quantity') === 1 ? 'item' : 'items' }}</span></h2>
        <div class="checkout-summary-items">
            @foreach($cart as $item)
                <div class="checkout-summary-item"><span class="checkout-summary-thumb">@if(!empty($item['image']))<img src="{{ \App\Support\MediaUrl::url('uploads/'.$item['image']) }}" alt="">@else ◉ @endif<b>{{ $item['quantity'] }}</b></span><span class="checkout-summary-name">{{ $item['name'] }}<small>KES {{ number_format($item['price']) }} each</small></span><b class="checkout-summary-price">KES {{ number_format($item['price'] * $item['quantity']) }}</b></div>
            @endforeach
        </div>
        <div class="coupon-box checkout-coupon">
            @if($couponCode)<div class="coupon-applied"><span><b>{{ $couponCode }}</b><small>Discount applied</small></span><a href="{{ route('remove-coupon', $couponCode) }}">Remove</a></div>
            @else<form method="post" action="{{ route('process_coupon') }}">@csrf<label class="sr-only" for="coupon-code">Coupon code</label><input id="coupon-code" name="code" placeholder="Discount code" required><button type="submit">Apply</button></form>@endif
            @if($errors->has('code'))<p class="form-error" role="alert">{{ $errors->first('code') }}</p>@endif
        </div>
        <div class="checkout-totals"><div><span>Subtotal</span><b>KES {{ number_format($subtotal) }}</b></div>@if($discount > 0)<div class="checkout-discount"><span>Discount</span><b>− KES {{ number_format($discount) }}</b></div>@endif<div class="checkout-delivery"><span>Delivery</span><span>Confirmed with you</span></div></div>
        <div class="summary-total checkout-grand-total"><span>Total</span><b>KES {{ number_format($total) }}</b></div>
        <button class="button button-dark full-button checkout-place-order" type="submit" form="checkout-order-form" data-place-order @if($checkoutStep !== 2) hidden @endif>Place order <span aria-hidden="true">→</span></button>
        <p class="checkout-summary-note"><span aria-hidden="true">⌑</span> Your payment method and delivery details are handled securely.</p>
        <a class="checkout-back-link" href="{{ route('cart') }}">← Return to your bag</a>
    </aside>
</section>
<script>
(()=>{
    const wizard=document.querySelector('[data-checkout-wizard]');
    if(!wizard)return;
    const panels=[...wizard.querySelectorAll('[data-step-panel]')];
    const indicators=[...document.querySelectorAll('[data-step-indicator]')];
    const placeOrder=document.querySelector('[data-place-order]');
    let current=Number(wizard.dataset.currentStep||0);
    const values=()=>({name:wizard.elements.name.value.trim(),email:wizard.elements.email.value.trim(),phone:wizard.elements.phone.value.trim(),address:wizard.elements.address.value.trim(),payment:wizard.querySelector('[name="payment_method"]:checked')?.value||''});
    const updateReview=()=>{const v=values();document.querySelector('[data-review-contact]').textContent=[v.name,v.email].filter(Boolean).join(' · ')||'Enter your contact details';document.querySelector('[data-review-delivery]').textContent=[v.phone,v.address].filter(Boolean).join(' · ')||'Enter your delivery information';document.querySelector('[data-review-payment]').textContent=v.payment==='econfirm'?'eConfirm Escrow · M-Pesa':v.payment==='cod'?'Cash on Delivery':'Select a payment method'};
    const showStep=(step,focus=true)=>{current=Math.max(0,Math.min(2,step));wizard.dataset.currentStep=String(current);panels.forEach((panel,index)=>{panel.hidden=index!==current});indicators.forEach((button,index)=>{button.classList.toggle('is-current',index===current);button.classList.toggle('is-complete',index<current);button.disabled=index>current; if(index===current)button.setAttribute('aria-current','step');else button.removeAttribute('aria-current')});placeOrder.hidden=current!==2;updateReview();if(focus)panels[current].querySelector('input,button')?.focus({preventScroll:true});};
    const currentFields=()=>[...panels[current].querySelectorAll('input[required]')];
    wizard.querySelectorAll('[data-step-next]').forEach(button=>button.addEventListener('click',()=>{for(const field of currentFields())if(!field.reportValidity())return;showStep(current+1)}));
    wizard.querySelectorAll('[data-step-back]').forEach(button=>button.addEventListener('click',()=>showStep(current-1)));
    wizard.querySelectorAll('[data-go-step]').forEach(button=>button.addEventListener('click',()=>showStep(Number(button.dataset.goStep))));
    indicators.forEach(button=>button.addEventListener('click',()=>{const step=Number(button.dataset.stepIndicator);if(step<=current)showStep(step)}));
    wizard.addEventListener('input',updateReview);wizard.addEventListener('change',updateReview);
    wizard.addEventListener('submit',event=>{if(current!==2){event.preventDefault();showStep(current)}});
    showStep(current,false);
})();
</script>
@endsection

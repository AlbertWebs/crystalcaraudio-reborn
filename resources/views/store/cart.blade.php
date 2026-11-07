@extends('store.layout')
@section('title','Shopping bag | Crystal Car Audio')
@section('content')
<section class="page-hero compact cart-page-hero">
    <p class="eyebrow">YOUR SELECTION</p>
    <h1>Your bag.</h1>
    @if(!$cart->isEmpty())<p>{{ $cart->sum('quantity') }} {{ $cart->sum('quantity') === 1 ? 'item' : 'items' }} ready for checkout</p>@endif
</section>
<section class="section cart-layout" aria-label="Shopping cart">
    @if($cart->isEmpty())
        <div class="empty-cart">
            <span aria-hidden="true">▣</span>
            <h2>Your bag is ready for something good.</h2>
            <p>Explore our car audio collection and find the right upgrade for your drive.</p>
            <a class="button button-dark" href="{{ route('categories') }}">Explore the collection <span aria-hidden="true">→</span></a>
        </div>
    @else
        <div class="cart-items">
            <form method="post" action="{{ route('cart.update') }}">
                @csrf
                <div class="cart-items-heading"><h2>Shopping bag</h2><a class="text-link" href="{{ route('categories') }}">Continue shopping</a></div>
                @foreach($cart as $id => $item)
                    <article class="cart-row">
                        <div class="cart-thumb">
                            @if(!empty($item['image']))<img loading="lazy" decoding="async" src="{{ \App\Support\MediaUrl::url('uploads/'.$item['image']) }}" alt="{{ $item['name'] }}">@else <span aria-hidden="true">◉</span> @endif
                        </div>
                        <div class="cart-product-info"><strong>{{ $item['name'] }}</strong><p>KES {{ number_format($item['price']) }} <span>each</span></p><a class="cart-remove-item" href="{{ route('remove.from.cart', $id) }}">Remove</a></div>
                        <label class="cart-quantity" for="quantity-{{ $id }}">Quantity<input id="quantity-{{ $id }}" type="number" min="0" max="10" name="quantity[{{ $id }}]" value="{{ $item['quantity'] }}" aria-label="Quantity for {{ $item['name'] }}"></label>
                        <b class="cart-line-total">KES {{ number_format($item['price'] * $item['quantity']) }}</b>
                    </article>
                @endforeach
                <button class="text-link update-cart" type="submit">Update bag <span aria-hidden="true">↻</span></button>
            </form>
        </div>
        <aside class="order-summary" aria-label="Order summary">
            <h2>Order summary</h2>
            <div><span>Subtotal</span><b>KES {{ number_format($total) }}</b></div>
            <div><span>Delivery</span><span class="summary-note">Calculated at checkout</span></div>
            <div class="summary-total"><span>Total</span><b>KES {{ number_format($total) }}</b></div>
            <a class="button button-dark full-button" href="{{ route('checkout') }}">Continue to checkout <span aria-hidden="true">→</span></a>
            <p class="cart-secure-note"><span aria-hidden="true">✓</span> Secure checkout · Delivery calculated at checkout</p>
            <a class="text-link cart-continue" href="{{ route('categories') }}">Continue shopping</a>
        </aside>
    @endif
</section>
@if($recommendations->isNotEmpty())
<section class="section cart-recommendations" aria-labelledby="cart-recommendations-title">
    <div class="cart-recommendations-heading"><div><p class="eyebrow">COMPLETE YOUR SETUP</p><h2 id="cart-recommendations-title">You can also buy</h2><p>Useful additions chosen to go with your bag.</p></div><a class="text-link" href="{{ route('categories') }}">Shop all products <span aria-hidden="true">→</span></a></div>
    <div class="product-grid cart-recommendations-grid">
        @foreach($recommendations as $product)
            @include('store.partials.product-card', ['product' => $product])
        @endforeach
    </div>
</section>
@endif
@endsection

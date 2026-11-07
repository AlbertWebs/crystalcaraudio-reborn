<div class="mini-cart-heading"><div><span class="eyebrow">YOUR SELECTION</span><h2>Your bag</h2></div><span class="mini-cart-count">{{ $cartCount }} {{ \Illuminate\Support\Str::plural('item', $cartCount) }}</span></div>
@if($cartItems->isNotEmpty())
    <div class="mini-cart-items">
        @foreach($cartItems as $item)
            <article class="mini-cart-item">
                <div class="mini-cart-thumb">@if(!empty($item['image']))<img loading="lazy" decoding="async" src="{{ str_starts_with($item['image'], 'http') ? $item['image'] : \App\Support\MediaUrl::url('uploads/'.$item['image']) }}" alt="">@else<span aria-hidden="true">◉</span>@endif</div>
                <div class="mini-cart-item-copy"><strong>{{ $item['name'] ?? 'Product' }}</strong><span>Qty {{ (int) ($item['quantity'] ?? 0) }}</span></div>
                <b>KES {{ number_format((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0)) }}</b>
            </article>
        @endforeach
    </div>
    <div class="mini-cart-subtotal"><span>Subtotal</span><strong>KES {{ number_format($cartSubtotal) }}</strong></div>
    <div class="mini-cart-actions"><a href="{{ route('cart') }}">View bag</a><a href="{{ route('checkout') }}">Checkout <span aria-hidden="true">↗</span></a></div>
@else
    <div class="mini-cart-empty"><span aria-hidden="true">▢</span><p>Your bag is empty.</p><a href="{{ route('categories') }}">Explore products <span aria-hidden="true">↗</span></a></div>
@endif

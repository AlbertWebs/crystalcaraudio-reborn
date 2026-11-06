@php
    $productUrl = route('product-single', $product->slug);
    $imagePath = $product->image;
    $discountPercent = $product->original_price && $product->original_price > $product->price
        ? (int) round((($product->original_price - $product->price) / $product->original_price) * 100)
        : null;
    $highlights = collect($product->specifications ?? [])->filter(fn ($spec) => filled($spec['title'] ?? null) && filled($spec['value'] ?? null))->take(2);
    $brandLabel = $product->brand && strtolower($product->brand) !== 'others' ? $product->brand : ($product->category ?: 'CRYSTAL AUDIO');
@endphp
<article class="product-card">
    <div class="product-card-media">
        <a class="product-image" href="{{ $productUrl }}" aria-label="View {{ $product->name }}">
            @if($imagePath)
                <img loading="lazy" src="{{ str_starts_with($imagePath, 'http') ? $imagePath : \App\Support\MediaUrl::url('uploads/'.$imagePath) }}" alt="{{ $product->name }}">
            @else
                <span class="product-placeholder">◉</span>
            @endif
        </a>
        @if($discountPercent || $product->is_offer || $product->is_special)
            <span class="product-badge badge-sale">{{ $discountPercent ? 'Flash Sale' : 'Special offer' }}</span>
        @elseif($product->featured || $product->trending)
            <span class="product-badge badge-popular">New Arrival</span>
        @endif
        @if($product->rating)
            <span class="product-rating-pill" aria-label="Rated {{ number_format($product->rating, 1) }} out of 5">{{ number_format($product->rating, 1) }} <b>★</b>@if($product->review_count) <small>({{ $product->review_count }})</small>@endif</span>
        @endif
        @auth
            <form class="wishlist-quick" method="post" action="{{ route('wishlist.add', $product) }}">
                @csrf
                <button type="submit" aria-label="Save {{ $product->name }} to wishlist">♡</button>
            </form>
        @endif
    </div>
    <div class="product-card-body">
        <div class="product-card-kicker"><span>{{ $brandLabel }}</span></div>
        <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
        @if($highlights->isNotEmpty())
            <ul class="product-highlights">
                @foreach($highlights as $highlight)<li><span>{{ \Illuminate\Support\Str::limit($highlight['title'], 23) }}</span><b title="{{ $highlight['value'] }}">{{ \Illuminate\Support\Str::limit($highlight['value'], 42) }}</b></li>@endforeach
            </ul>
        @endif
        <div class="product-card-buy-row"><div class="product-price-row"><strong>KES {{ number_format($product->price) }}</strong>@if($discountPercent)<del>KES {{ number_format($product->original_price) }}</del>@endif</div>
        <form class="card-add-form" method="post" action="{{ route('cart.add', $product) }}">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button class="button button-dark" type="submit" aria-label="Add {{ $product->name }} to bag" @if(str_contains(strtolower((string) $product->stock), 'out')) disabled @endif><svg class="card-cart-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.5 4.5h2.2l2 9.7a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.5L21 8H6.2"/><circle cx="10.2" cy="19" r="1.15"/><circle cx="18" cy="19" r="1.15"/></svg></button>
        </form>
        </div>
    </div>
</article>

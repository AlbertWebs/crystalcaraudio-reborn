@extends('store.layout')
@section('title','My account | Crystal Car Audio')
@section('content')
<main class="customer-dashboard">
    <header class="customer-dashboard-head">
        <div><p class="eyebrow">YOUR CRYSTAL ACCOUNT</p><h1>Good to see you, {{ Str::before($user->name, ' ') }}.</h1><p>Manage your orders, delivery details and saved products.</p></div>
        <a class="button button-dark" href="{{ route('categories') }}">Shop products <span aria-hidden="true">→</span></a>
    </header>

    @if(session('success'))<p class="dashboard-alert dashboard-success" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="dashboard-alert dashboard-error" role="alert">{{ $errors->first() }}</p>@endif

    <section class="dashboard-stats" aria-label="Account overview">
        <article><span class="dashboard-stat-icon" aria-hidden="true">↗</span><div><small>ORDERS PLACED</small><strong>{{ number_format($orderStats->count ?? 0) }}</strong></div></article>
        <article><span class="dashboard-stat-icon" aria-hidden="true">KES</span><div><small>ORDER TOTAL</small><strong>KES {{ number_format($orderStats->total ?? 0) }}</strong></div></article>
        <article><span class="dashboard-stat-icon" aria-hidden="true">♡</span><div><small>SAVED PRODUCTS</small><strong>{{ number_format($wishlist->count()) }}</strong></div></article>
    </section>

    <div class="dashboard-columns">
        <div class="dashboard-primary">
            <section class="dashboard-panel dashboard-orders">
                <div class="dashboard-panel-heading"><div><p class="eyebrow">YOUR PURCHASES</p><h2>Recent orders</h2></div><span>{{ $orders->count() }} shown</span></div>
                @forelse($orders as $order)
                    @php
                        $status = $order->payment_status === 'escrow_held' ? 'Payment secured' : ucwords(str_replace('_', ' ', $order->order_status ?: $order->payment_status));
                        $statusClass = in_array($order->order_status, ['delivered','completed'], true) ? 'is-complete' : ($order->payment_status === 'escrow_held' ? 'is-secured' : 'is-pending');
                    @endphp
                    <article class="dashboard-order">
                        <div class="dashboard-order-top"><div><strong>{{ $order->reference }}</strong><span>{{ optional($order->created_at)->format('M j, Y') }}</span></div><span class="dashboard-status {{ $statusClass }}">{{ $status }}</span></div>
                        <div class="dashboard-order-bottom"><span>{{ count($order->items ?? []) }} {{ count($order->items ?? []) === 1 ? 'item' : 'items' }} <i>·</i> {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'eConfirm Escrow' }}</span><strong>KES {{ number_format($order->total) }}</strong></div>
                        @if(!empty($order->items))<details class="dashboard-order-items"><summary>View order items</summary><div>@foreach($order->items as $item)<p><span>{{ $item['name'] ?? 'Product' }} <small>× {{ $item['quantity'] ?? 1 }}</small></span><b>KES {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) }}</b></p>@endforeach</div></details>@endif
                    </article>
                @empty
                    <div class="dashboard-empty"><span aria-hidden="true">⌑</span><h3>Your first order starts here.</h3><p>When you place an order, its status and details will show up here.</p><a href="{{ route('categories') }}" class="button button-dark">Explore products →</a></div>
                @endforelse
            </section>

            @if($legacyOrders->isNotEmpty())
                <section class="dashboard-panel"><div class="dashboard-panel-heading"><div><p class="eyebrow">ARCHIVE</p><h2>Previous orders</h2></div></div>
                    @foreach($legacyOrders as $order)<article class="dashboard-order dashboard-order-legacy"><div class="dashboard-order-top"><div><strong>Order #{{ $order['id'] ?? '—' }}</strong><span>{{ $order['created_at'] ?? 'Previous order' }}</span></div><span class="dashboard-status">{{ ucfirst($order['status'] ?? 'Archived') }}</span></div><div class="dashboard-order-bottom"><span>Order history</span><strong>KES {{ number_format((float)($order['total'] ?? 0)) }}</strong></div></article>@endforeach
                </section>
            @endif

            <section class="dashboard-panel dashboard-wishlist">
                <div class="dashboard-panel-heading"><div><p class="eyebrow">SAVED FOR LATER</p><h2>Your wishlist</h2></div><a href="{{ route('categories') }}">Discover more ↗</a></div>
                @if($wishlist->isNotEmpty())<div class="dashboard-wishlist-grid">@foreach($wishlist as $product)
                    <article class="dashboard-wishlist-product"><a href="{{ route('product-single',$product->slug) }}" class="dashboard-wishlist-image">@if($product->image)<img src="{{ str_starts_with($product->image, 'http') ? $product->image : \App\Support\MediaUrl::url('uploads/'.$product->image) }}" alt="{{ $product->name }}">@else<span aria-hidden="true">◉</span>@endif</a><div><a href="{{ route('product-single',$product->slug) }}" class="dashboard-wishlist-name">{{ $product->name }}</a><strong>KES {{ number_format($product->price) }}</strong><form method="post" action="{{ route('wishlist.remove',$product->id) }}">@csrf @method('DELETE')<button type="submit">Remove from saved</button></form></div></article>
                @endforeach</div>@else<p class="dashboard-muted">Tap the heart on a product to save it here.</p>@endif
            </section>
        </div>

        <aside class="dashboard-panel dashboard-profile">
            <div class="dashboard-panel-heading"><div><p class="eyebrow">ACCOUNT DETAILS</p><h2>Your information</h2></div><span class="dashboard-avatar" aria-hidden="true">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</span></div>
            <p class="dashboard-muted">Keep these details current so we can reach you about delivery.</p>
            <form method="post" action="{{ route('dashboard.update') }}">@csrf
                <label for="dashboard-name">Full name<input id="dashboard-name" required name="name" value="{{ old('name',$user->name) }}" autocomplete="name"></label>
                <label for="dashboard-email">Email address<input id="dashboard-email" disabled value="{{ $user->email }}" autocomplete="email"><small>Email is used to sign in and identify your orders.</small></label>
                <label for="dashboard-phone">Phone number<input id="dashboard-phone" name="phone" value="{{ old('phone',$user->phone) }}" autocomplete="tel" placeholder="+254 7XX XXX XXX"></label>
                <label for="dashboard-address">Delivery address<textarea id="dashboard-address" name="address" rows="3" autocomplete="street-address" placeholder="Street, estate, town">{{ old('address',$user->address) }}</textarea></label>
                <button class="button button-dark" type="submit">Save details <span aria-hidden="true">→</span></button>
            </form>
            <a class="dashboard-reset-link" href="{{ route('password.request') }}">Change password</a>
            <form class="dashboard-logout" method="post" action="{{ route('customer.logout') }}">@csrf<button type="submit">Sign out</button></form>
        </aside>
    </div>
</main>
<style>
.customer-dashboard{max-width:1320px;margin:0 auto;padding:48px 28px 82px;font-family:'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;color:#202622}
.customer-dashboard-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:27px}
.customer-dashboard-head .eyebrow,.dashboard-panel .eyebrow{margin:0 0 6px;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.13em}
.customer-dashboard-head h1{margin:0;font:600 clamp(30px,4vw,42px)/1.12 'Outfit SemiBold','Outfit','Cairo',sans-serif;letter-spacing:-.04em}
.customer-dashboard-head>div>p:last-child{margin:8px 0 0;color:#858f89;font:400 15px/22px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}
.customer-dashboard-head .button{min-height:46px;padding:10px 16px;font-size:14px;white-space:nowrap}
.dashboard-alert{margin:0 0 18px;padding:12px 15px;border-radius:8px;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-success{background:#f0faf4;color:#286e50}.dashboard-error{background:#fff5f5;color:#a13d3d}
.dashboard-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}
.dashboard-stats article{min-height:94px;padding:17px;display:flex;align-items:center;gap:14px;border:1px solid #e4ebe7;border-radius:11px;background:linear-gradient(135deg,#fff,#f8fbf9)}
.dashboard-stat-icon{width:41px;height:41px;flex:0 0 41px;display:grid;place-items:center;border-radius:12px;background:#e9f4f1;color:#0c6661;font:600 14px/1 'Outfit SemiBold','Outfit','Cairo',sans-serif}
.dashboard-stats small{display:block;color:#849089;font:500 12px/17px 'Outfit Medium','Outfit','Cairo',sans-serif;letter-spacing:.07em}.dashboard-stats strong{display:block;margin-top:3px;color:#24312b;font:600 19px/25px 'Outfit SemiBold','Outfit','Cairo',sans-serif}
.dashboard-columns{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.75fr);gap:18px;align-items:start}
.dashboard-primary{display:grid;gap:17px;min-width:0}
.dashboard-panel{padding:21px;border:1px solid #e4ebe7;border-radius:12px;background:#fff;box-shadow:0 5px 18px rgba(20,48,40,.03);min-width:0}
.dashboard-panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:13px}.dashboard-panel-heading h2{margin:0;color:#26322c;font:600 21px/27px 'Outfit SemiBold','Outfit','Cairo',sans-serif}.dashboard-panel-heading>span,.dashboard-panel-heading>a{color:#818d86;font:400 12px/18px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-panel-heading>a{color:#0c6661;text-decoration:underline;text-underline-offset:3px}
.dashboard-order{padding:14px 0;border-top:1px solid #edf0ee}.dashboard-order-top,.dashboard-order-bottom{display:flex;justify-content:space-between;align-items:center;gap:12px}.dashboard-order-top>div{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.dashboard-order-top strong{color:#25312b;font:500 14px/20px 'Outfit Medium','Outfit','Cairo',sans-serif}.dashboard-order-top>div>span,.dashboard-order-bottom>span{color:#849089;font:400 13px/19px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-order-bottom{margin-top:9px}.dashboard-order-bottom>strong{color:#202b25;font:600 16px/22px 'Outfit SemiBold','Outfit','Cairo',sans-serif;white-space:nowrap}.dashboard-order-bottom i{padding:0 5px;color:#b6c0ba;font-style:normal}
.dashboard-status{padding:5px 9px;border-radius:20px;background:#f0f4f2;color:#67756d;font:500 12px/17px 'Outfit Medium','Outfit','Cairo',sans-serif;white-space:nowrap}.dashboard-status.is-pending{background:#fff7e8;color:#966b1e}.dashboard-status.is-secured{background:#eaf6f0;color:#247454}.dashboard-status.is-complete{background:#eaf6f0;color:#247454}
.dashboard-order-items{margin-top:10px;color:#0c6661;font:500 12px/18px 'Outfit Medium','Outfit','Cairo',sans-serif}.dashboard-order-items summary{width:max-content;cursor:pointer}.dashboard-order-items>div{margin-top:8px;padding:7px 11px;border-radius:7px;background:#f8faf9}.dashboard-order-items p{display:flex;justify-content:space-between;gap:10px;margin:0;padding:6px 0;color:#56635b;font:400 13px/19px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-order-items small{color:#89938d}.dashboard-order-items b{white-space:nowrap;font-weight:500}
.dashboard-empty{padding:28px 15px;text-align:center}.dashboard-empty>span{width:42px;height:42px;margin:0 auto 10px;display:grid;place-items:center;border-radius:50%;background:#edf5f2;color:#0c6661;font-size:22px}.dashboard-empty h3{margin:0;color:#2a342f;font:600 17px/23px 'Outfit SemiBold','Outfit','Cairo',sans-serif}.dashboard-empty p,.dashboard-muted{color:#87918b;font:400 14px/21px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-empty .button{min-height:42px;margin-top:5px;font-size:13px}.dashboard-order-legacy .dashboard-order-bottom{margin-top:8px}
.dashboard-wishlist-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.dashboard-wishlist-product{display:grid;grid-template-columns:70px minmax(0,1fr);gap:11px;padding:10px;border:1px solid #edf0ee;border-radius:9px}.dashboard-wishlist-image{height:70px;display:grid;place-items:center;overflow:hidden;border-radius:7px;background:#f6f7f6;color:#0c6661}.dashboard-wishlist-image img{width:100%;height:100%;object-fit:contain;mix-blend-mode:multiply}.dashboard-wishlist-product>div{min-width:0;display:flex;flex-direction:column;align-items:flex-start}.dashboard-wishlist-name{display:-webkit-box;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:2;color:#28342d;font:500 13px/18px 'Outfit Medium','Outfit','Cairo',sans-serif}.dashboard-wishlist-product>div>strong{margin-top:4px;color:#27332d;font:600 14px/19px 'Outfit SemiBold','Outfit','Cairo',sans-serif}.dashboard-wishlist-product form{margin-top:auto}.dashboard-wishlist-product button,.dashboard-logout button{padding:0;border:0;background:none;color:#0c6661;font:400 12px/18px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;text-decoration:underline;text-underline-offset:3px;cursor:pointer}
.dashboard-profile{position:sticky;top:105px}.dashboard-avatar{width:40px;height:40px;display:grid;place-items:center;border-radius:50%;background:#e9f4f1;color:#0c6661;font:600 16px/1 'Outfit SemiBold','Outfit','Cairo',sans-serif}.dashboard-profile>.dashboard-muted{margin:0 0 18px}.dashboard-profile form:not(.dashboard-logout){display:grid;gap:12px}.dashboard-profile label{display:block;color:#424d46;font:500 13px/19px 'Outfit Medium','Outfit','Cairo',sans-serif}.dashboard-profile input,.dashboard-profile textarea{display:block;width:100%;margin-top:5px;padding:11px 12px;border:1px solid #dfe7e3;border-radius:7px;background:#fff;color:#28332d;font:400 14px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;resize:vertical}.dashboard-profile input:focus,.dashboard-profile textarea:focus{outline:2px solid rgba(12,102,97,.13);border-color:#0c6661}.dashboard-profile input:disabled{background:#f6f8f7;color:#7b8780}.dashboard-profile label small{display:block;margin-top:4px;color:#89938d;font:400 12px/17px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif}.dashboard-profile .button{min-height:43px;margin-top:3px;justify-content:center;font-size:14px}.dashboard-reset-link{display:block;margin-top:16px;color:#0c6661;font:400 13px/20px 'Outfit Regular','Cairo Regular','Helvetica Neue',Helvetica,Arial,sans-serif;text-decoration:underline;text-underline-offset:3px}.dashboard-logout{margin-top:17px;padding-top:15px;border-top:1px solid #edf0ee}.dashboard-logout button{color:#777;font-size:13px}
@media(max-width:850px){.customer-dashboard{padding:34px 20px 65px}.dashboard-columns{grid-template-columns:minmax(0,1fr) 290px}.dashboard-wishlist-grid{grid-template-columns:1fr}}
@media(max-width:700px){.customer-dashboard{padding:28px 15px 55px}.customer-dashboard-head{align-items:flex-start;flex-direction:column;margin-bottom:20px}.customer-dashboard-head .button{align-self:stretch;justify-content:center}.dashboard-stats{gap:8px}.dashboard-stats article{min-height:84px;flex-direction:column;align-items:flex-start;gap:8px;padding:11px}.dashboard-stat-icon{width:33px;height:33px;flex-basis:33px;border-radius:10px;font-size:12px}.dashboard-stats small{font-size:12px;letter-spacing:0}.dashboard-stats strong{font-size:15px;line-height:20px}.dashboard-columns{grid-template-columns:1fr;gap:15px}.dashboard-primary{order:1}.dashboard-profile{position:static;order:0}.dashboard-panel{padding:17px}.dashboard-wishlist-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:430px){.customer-dashboard{padding-inline:12px}.customer-dashboard-head h1{font-size:30px}.customer-dashboard-head>div>p:last-child{font-size:14px}.dashboard-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.dashboard-stats article{padding:9px 8px}.dashboard-stats small{font-size:12px}.dashboard-stats strong{font-size:13px}.dashboard-order-top,.dashboard-order-bottom{align-items:flex-start}.dashboard-order-top{flex-direction:column;gap:8px}.dashboard-order-top>div{justify-content:space-between;width:100%}.dashboard-order-bottom>span{max-width:65%}.dashboard-wishlist-grid{grid-template-columns:1fr}.dashboard-wishlist-product{grid-template-columns:65px minmax(0,1fr)}.dashboard-wishlist-image{height:65px}}
</style>
@endsection

@extends('store.layout')
@section('title', 'Order received | Crystal Car Audio')
@section('content')
@php
    $cashOrder = $order->payment_method === 'cod';
    $paymentConfirmed = $order->payment_status === 'escrow_held';
    $waitingForPayment = ! $cashOrder && ! $paymentConfirmed;
    $statusTitle = $cashOrder ? 'Order received' : ($paymentConfirmed ? 'Payment protected' : 'Payment confirmation in progress');
@endphp
<section class="complete-page complete-page-v2" @if($waitingForPayment) data-reconcile-url="{{ route('checkout.reconcile',$order->reference) }}" data-complete-url="{{ route('checkout.complete',$order->reference) }}" @endif>
    <div class="complete-main-card">
        <div class="complete-status-icon {{ $waitingForPayment ? 'is-pending' : '' }}" aria-hidden="true">
            @if($waitingForPayment)<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            @else<svg viewBox="0 0 24 24"><path d="m5 12 4.5 4.5L19 7"/></svg>@endif
        </div>
        <p class="eyebrow">{{ $waitingForPayment ? 'SECURE PAYMENT STATUS' : 'THANK YOU FOR CHOOSING CRYSTAL' }}</p>
        <h1>{{ $statusTitle }}</h1>
        <p class="complete-intro">
            @if($cashOrder)
                Your order is confirmed. Pay when it arrives and our team will contact you to coordinate delivery.
            @elseif($paymentConfirmed)
                Your payment is safely held in eConfirm escrow. We’ll keep you updated as your order moves forward.
            @else
                Your order is saved while we securely verify your eConfirm payment. This page will update automatically.
            @endif
        </p>
        @if(!empty($accountNotice))<p class="complete-account-notice" role="status"><span aria-hidden="true">✉</span>{{ $accountNotice }} <a href="{{ route('login') }}">Sign in</a></p>@endif
        <div class="complete-progress" aria-label="Order progress">
            <div class="is-done"><i>✓</i><span>Order placed</span></div><b></b>
            <div class="{{ $cashOrder || $paymentConfirmed ? 'is-done' : 'is-current' }}"><i>{{ $cashOrder || $paymentConfirmed ? '✓' : '02' }}</i><span>{{ $cashOrder ? 'Pay on delivery' : 'Payment' }}</span></div><b></b>
            <div class="{{ $paymentConfirmed ? 'is-done' : '' }}"><i>{{ $paymentConfirmed ? '✓' : '03' }}</i><span>Delivery</span></div>
        </div>
        <div class="complete-actions">
            <a href="{{ route('categories') }}" class="button button-dark">Continue shopping <span aria-hidden="true">→</span></a>
            <a href="{{ route('page.find-us') }}" class="complete-help-link">Need help? Contact Crystal</a>
        </div>
        @if($waitingForPayment)<p class="complete-live-status" role="status" aria-live="polite"><span></span>Checking payment securely…</p>@endif
    </div>
    <aside class="complete-order-card" aria-label="Order details">
        <div class="complete-order-heading"><div><span>ORDER SUMMARY</span><h2>Your order</h2></div><span class="complete-order-count">{{ count($order->items ?? []) }} {{ count($order->items ?? []) === 1 ? 'item' : 'items' }}</span></div>
        <p class="complete-reference"><span>Order number</span><strong>{{ $order->reference }}</strong></p>
        <div class="complete-item-list">
            @foreach($order->items ?? [] as $item)
                <div class="complete-order-item">
                    <span class="complete-item-image">@if(!empty($item['image']))<img src="{{ \App\Support\MediaUrl::url('uploads/'.$item['image']) }}" alt="">@else<span aria-hidden="true">◉</span>@endif</span>
                    <span class="complete-item-name">{{ $item['name'] ?? 'Product' }}<small>Qty {{ $item['quantity'] ?? 1 }}</small></span>
                    <strong>KES {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) }}</strong>
                </div>
            @endforeach
        </div>
        <div class="complete-detail-row"><span>Payment method</span><strong>{{ $cashOrder ? 'Cash on Delivery' : 'eConfirm Escrow' }}</strong></div>
        @if($order->address)<div class="complete-detail-row complete-address"><span>Delivery address</span><strong>{{ $order->address }}</strong></div>@endif
        <div class="complete-total"><span>Total</span><strong>KES {{ number_format($order->total) }}</strong></div>
        <p class="complete-secure-note"><span aria-hidden="true">✓</span> Your order details are saved securely.</p>
    </aside>
</section>
@endsection

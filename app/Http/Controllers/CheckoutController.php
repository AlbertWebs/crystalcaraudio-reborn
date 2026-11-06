<?php

namespace App\Http\Controllers;

use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = collect(session('cart', []));
        if ($cart->isEmpty()) return redirect()->route('categories')->with('success', 'Your cart is empty.');
        $subtotal = round($cart->sum(fn ($item) => $item['price'] * $item['quantity']), 2);
        $discount = min((float) session('coupon_discount', 0), $subtotal);
        return view('store.checkout', ['cart' => $cart, 'subtotal' => $subtotal, 'discount' => $discount, 'couponCode' => session('coupon_code'), 'total' => round($subtotal - $discount, 2), 'checkoutDetails' => session('checkout_details', [])]);
    }

    public function processCoupon(Request $request)
    {
        $data = $request->validate(['code' => ['required','string','max:80']]);
        $cart = collect(session('cart', []));
        if ($cart->isEmpty()) return redirect()->route('cart')->withErrors(['code' => 'Add products to your bag first.']);
        $coupon = DB::table('catalog_coupons')->whereRaw('LOWER(code) = ?', [strtolower(trim($data['code']))])->where('is_active', true)->first();
        if (! $coupon || ($coupon->expires_at && now()->greaterThan($coupon->expires_at))) return back()->withErrors(['code' => 'That coupon is invalid or has expired.']);
        $subtotal = $cart->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discount = min(round($subtotal * min((float) $coupon->percent_off, 100) / 100, 2), $subtotal);
        session(['coupon_code' => $coupon->code, 'coupon_discount' => $discount]);
        return back()->with('success', 'Coupon '.$coupon->code.' applied.');
    }

    public function removeCoupon(string $code)
    {
        if (strcasecmp((string) session('coupon_code'), $code) === 0) session()->forget(['coupon_code','coupon_discount']);
        return redirect()->route('checkout');
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:160'], 'email' => ['required','email','max:190'],
            'phone' => ['required','string','max:24'], 'address' => ['required','string','max:1000'],
            'payment_method' => ['required', Rule::in(['cod','econfirm'])],
            'create_account' => ['sometimes','accepted'],
        ]);
        // Keep a server-side draft so a failed payment attempt can be resumed.
        session(['checkout_details' => [
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'],
            'address' => $data['address'], 'payment_method' => $data['payment_method'],
            'create_account' => $request->boolean('create_account'),
        ]]);
        $cart = collect(session('cart', []));
        abort_if($cart->isEmpty(), 422, 'Your cart is empty.');
        $subtotal = round($cart->sum(fn ($item) => $item['price'] * $item['quantity']), 2);
        $couponCode = session('coupon_code');
        $coupon = $couponCode ? DB::table('catalog_coupons')->where('code', $couponCode)->where('is_active', true)->first() : null;
        if ($couponCode && ! $coupon) return redirect()->route('checkout')->withErrors(['code' => 'That coupon is no longer available.']);
        $discount = $coupon ? min((float) session('coupon_discount', 0), $subtotal) : 0;
        $total = round($subtotal - $discount, 2);
        $reference = 'CCA-'.Str::upper(Str::random(10));
        $order = StoreOrder::create([
            'reference' => $reference, 'customer_name' => $data['name'], 'email' => $data['email'],
            'phone' => $data['phone'], 'address' => $data['address'], 'items' => $cart->values()->all(),
            'total' => $total, 'payment_method' => $data['payment_method'],
            'coupon_code' => $couponCode, 'discount' => $discount,
            'payment_status' => 'pending_payment', 'order_status' => 'pending',
            'merchant_reference' => $reference,
        ]);
        if ($request->boolean('create_account')) {
            $this->createCheckoutAccount($data);
        }
        if ($coupon) DB::table('catalog_coupons')->where('id', $coupon->id)->update(['is_active' => false, 'updated_at' => now()]);

        if ($data['payment_method'] === 'cod') {
            session()->forget('cart');
            session()->forget(['coupon_code','coupon_discount']);
            session()->forget('checkout_details');
            session(['active_checkout_order' => $order->reference]);
            return redirect()->route('checkout.complete', $order->reference);
        }

        $apiKey = config('services.econfirm.api_key');
        $sellerEmail = config('services.econfirm.seller_email');
        $receiverPhone = config('services.econfirm.receiver_phone');
        if (! $apiKey || ! $sellerEmail || ! $receiverPhone) {
            return redirect()->route('checkout')->withErrors(['payment_method' => 'eConfirm is temporarily unavailable. Your details are saved; try again or choose Cash on Delivery.'])->withInput($data);
        }
        if ($total < 100) {
            return redirect()->route('checkout')->withErrors(['payment_method' => 'eConfirm escrow requires a minimum order of KES 100. Your details are saved.'])->withInput($data);
        }

        $base = rtrim(config('services.econfirm.api_v2_url'), '/');
        $headers = ['Authorization' => 'Bearer '.$apiKey, 'Accept' => 'application/json'];
        try {
            $escrow = Http::withHeaders($headers)->timeout(20)->post($base.'/transactions', [
                'amount' => $total, 'currency' => 'KES', 'buyer_email' => $data['email'],
                'seller_email' => $sellerEmail, 'receiver_phone' => $receiverPhone,
                'description' => 'Crystal Car Audio order '.$reference,
                'terms' => 'Payment held in escrow for order '.$reference,
            ])->throw()->json('data');

            $order->update(['econfirm_transaction_id' => $escrow['id']]);

            $checkout = Http::withHeaders($headers + ['Idempotency-Key' => 'order:'.$reference.':checkout-v1'])->timeout(20)->post($base.'/checkout/sessions', [
                'transaction_id' => $escrow['id'], 'return_url' => route('checkout.complete', $reference, true).'?checkout_return=1',
                'merchant_reference' => $reference, 'webhook_url' => route('webhooks.econfirm', [], true),
            ])->throw()->json('data');
        } catch (\Illuminate\Http\Client\ConnectionException|\Illuminate\Http\Client\RequestException $exception) {
            report($exception);
            return redirect()->route('checkout')->withErrors(['payment_method' => 'We could not start secure payment. Your details are saved; please try again.'])->withInput($data);
        }

        $order->update(['econfirm_transaction_id' => $escrow['id'], 'econfirm_session_id' => $checkout['id'] ?? null, 'econfirm_webhook_secret' => $checkout['webhook_secret'] ?? null]);
        session(['active_checkout_order' => $order->reference, 'econfirm_checkout_session' => [
            'checkout_url' => $checkout['checkout_url'], 'checkout_secret' => $checkout['checkout_secret'],
        ]]);
        return redirect()->route('checkout.pay', $order->reference);
    }

    private function createCheckoutAccount(array $details): void
    {
        try {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($details['email'])])->first();
            if ($user) {
                session()->put('checkout_account_notice', 'An account already uses this email address. You can sign in to view your order history.');
                return;
            }

            $user = User::create([
                'name' => $details['name'],
                'email' => $details['email'],
                'password' => Hash::make(Str::random(48)),
            ]);
            DB::table('users')->where('id', $user->id)->update([
                'phone' => $details['phone'], 'address' => $details['address'],
            ]);

            $mailStatus = Password::sendResetLink(['email' => $user->email]);
            if ($mailStatus === Password::RESET_LINK_SENT) {
                session()->put('checkout_account_notice', 'Your account is ready. Check your email for a secure link to set your password and sign in.');
            } else {
                session()->put('checkout_account_notice', 'Your account was created, but we could not send the password setup email. Use Forgot password on the sign-in page to set your password.');
            }
        } catch (\Throwable $exception) {
            report($exception);
            session()->put('checkout_account_notice', 'Your order is saved, but account setup could not be completed. You can create an account from the sign-in page later.');
        }
    }

    public function pay(string $reference)
    {
        $order = StoreOrder::where('reference', $reference)->firstOrFail();
        abort_unless(session('active_checkout_order') === $order->reference, 403);
        $checkout = session('econfirm_checkout_session');
        abort_unless($checkout && $order->payment_method === 'econfirm', 404);
        return view('store.econfirm', compact('order', 'checkout'));
    }

    public function reconcile(Request $request, string $reference)
    {
        $order = StoreOrder::where('reference', $reference)->firstOrFail();
        abort_unless(session('active_checkout_order') === $order->reference, 403);
        if ($order->payment_status === 'escrow_held') return response()->json(['status' => 'funded', 'redirect_url' => route('checkout.complete', $order->reference)]);
        $key = config('services.econfirm.api_key');
        if ($key && $order->econfirm_transaction_id) {
            $result = Http::withToken($key)->acceptJson()->timeout(15)->get(rtrim(config('services.econfirm.api_v2_url'), '/').'/transactions/'.$order->econfirm_transaction_id)->throw()->json('data');
            $verifiedStatus = in_array($result['status'] ?? '', ['Escrow Funded', 'Completed'], true);
            if (($result['id'] ?? null) === $order->econfirm_transaction_id && $verifiedStatus && abs((float) ($result['amount'] ?? 0) - (float) $order->total) < 0.01 && strtoupper($result['currency'] ?? '') === 'KES') {
                $order->update(['payment_status' => 'escrow_held']);
                return response()->json(['status' => 'funded', 'redirect_url' => route('checkout.complete', $order->reference)]);
            }
        }
        return response()->json(['status' => 'pending']);
    }

    public function complete(string $reference)
    {
        $order = StoreOrder::where('reference', $reference)->firstOrFail();
        abort_unless(session('active_checkout_order') === $order->reference, 403);
        $accountNotice = session()->pull('checkout_account_notice');
        if ($order->payment_method === 'cod') {
            session()->forget('cart');
            session()->forget('checkout_details');
        } elseif ($order->payment_status === 'escrow_held') {
            session()->forget(['econfirm_checkout_session', 'checkout_details']);
        }
        return view('store.complete', compact('order', 'accountNotice'));
    }

    public function webhook(Request $request)
    {
        $event = json_decode($request->getContent(), true);
        abort_unless(is_array($event), 400);
        $reference = (string) ($event['data']['merchant_reference'] ?? '');
        $order = StoreOrder::where('merchant_reference', $reference)->first();
        $secret = (string) ($order?->econfirm_webhook_secret ?? '');
        abort_unless($secret !== '', 401);
        $timestamp = (int) $request->header('X-eConfirm-Timestamp');
        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        abort_unless($timestamp > 0 && abs(now()->timestamp - $timestamp) <= 300 && hash_equals($expected, (string) $request->header('X-eConfirm-Signature')), 401);
        abort_unless($request->header('X-eConfirm-Event') === 'checkout.funded' && ($event['type'] ?? null) === 'checkout.funded' && ($event['id'] ?? null) === $request->header('X-eConfirm-Event-Id'), 400);
        abort_unless(($event['data']['transaction_id'] ?? null) === $order->econfirm_transaction_id, 409);
        abort_unless(is_numeric($event['data']['amount_kes'] ?? null) && abs((float) $event['data']['amount_kes'] - (float) $order->total) < 0.01 && strtoupper((string) ($event['data']['currency'] ?? '')) === 'KES', 409);
        \Illuminate\Support\Facades\DB::transaction(function () use ($event, $order) {
            if (\Illuminate\Support\Facades\DB::table('econfirm_webhook_events')->where('event_id', $event['id'])->exists()) return;
            $locked = StoreOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $locked->update(['payment_status' => 'escrow_held']);
            \Illuminate\Support\Facades\DB::table('econfirm_webhook_events')->insert(['event_id' => $event['id'], 'created_at' => now(), 'updated_at' => now()]);
        });
        return response()->noContent();
    }
}

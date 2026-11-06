<?php

namespace Tests\Feature;

use App\Models\CatalogProduct;
use App\Models\StoreOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_a_product_and_place_a_cod_order(): void
    {
        $product = CatalogProduct::create([
            'name' => 'Road Speaker Set', 'slug' => 'road-speaker-set', 'category' => 'Speakers',
            'price' => 4500, 'stock' => 'In Stock',
        ]);

        $this->get('/')->assertOk()->assertSee('Road Speaker Set');
        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect(route('cart'));
        $this->get(route('checkout'))->assertOk()->assertSee('Cash on Delivery');

        $response = $this->post(route('checkout.order'), [
            'name' => 'Test Customer', 'email' => 'customer@example.test',
            'phone' => '+254700000000', 'address' => 'Nairobi', 'payment_method' => 'cod',
        ]);

        $order = StoreOrder::firstOrFail();
        $response->assertRedirect(route('checkout.complete', $order->reference));
        $this->assertSame(9000.0, (float) $order->total);
        $this->assertSame('pending_payment', $order->payment_status);
        $this->assertSame([], session('cart', []));
        $this->get(route('checkout.complete', $order->reference))->assertOk()->assertSee($order->reference);
    }

    public function test_econfirm_webhook_requires_a_valid_signature_and_is_idempotent(): void
    {
        $order = StoreOrder::create([
            'reference' => 'CCA-ESCROW-TEST', 'merchant_reference' => 'CCA-ESCROW-TEST',
            'customer_name' => 'Test Customer', 'email' => 'customer@example.test', 'phone' => '+254700000000',
            'address' => 'Nairobi', 'items' => [], 'total' => 1500,
            'payment_method' => 'econfirm', 'payment_status' => 'pending_payment', 'order_status' => 'pending',
            'econfirm_transaction_id' => 'E-TEST-123', 'econfirm_webhook_secret' => 'test-webhook-secret',
        ]);
        $event = [
            'id' => 'event-test-123', 'type' => 'checkout.funded',
            'data' => ['transaction_id' => 'E-TEST-123', 'merchant_reference' => $order->reference, 'amount_kes' => 1500, 'currency' => 'KES'],
        ];
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, 'test-webhook-secret');
        $server = [
            'HTTP_X_ECONFIRM_TIMESTAMP' => $timestamp,
            'HTTP_X_ECONFIRM_SIGNATURE' => $signature,
            'HTTP_X_ECONFIRM_EVENT' => 'checkout.funded',
            'HTTP_X_ECONFIRM_EVENT_ID' => $event['id'],
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
        ];

        $this->call('POST', '/api/webhooks/econfirm/checkout', [], [], [], $server, $body)->assertNoContent();
        $this->call('POST', '/api/webhooks/econfirm/checkout', [], [], [], $server, $body)->assertNoContent();

        $this->assertSame('escrow_held', $order->fresh()->payment_status);
        $this->assertDatabaseCount('econfirm_webhook_events', 1);
    }
}

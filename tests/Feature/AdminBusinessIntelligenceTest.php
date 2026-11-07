<?php

namespace Tests\Feature;

use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_operational_dashboard_with_real_metrics(): void
    {
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create(['email' => 'admin@crystalcaraudio.com']);
        StoreOrder::create([
            'reference' => 'CCA-PAID-001', 'customer_name' => 'Amina Wanjiku', 'email' => 'amina@example.test',
            'phone' => '+254700000001', 'address' => 'Nairobi', 'items' => [], 'total' => 4200,
            'payment_method' => 'cod', 'payment_status' => 'paid', 'order_status' => 'completed',
        ]);
        StoreOrder::create([
            'reference' => 'CCA-PENDING-001', 'customer_name' => 'Brian Otieno', 'email' => 'brian@example.test',
            'phone' => '+254700000002', 'address' => 'Nairobi', 'items' => [], 'total' => 1800,
            'payment_method' => 'cod', 'payment_status' => 'pending_payment', 'order_status' => 'pending',
        ]);

        $this->actingAs($admin)->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Total revenue')
            ->assertSee('KES 4,200')
            ->assertSee('Pending orders')
            ->assertSee('Business intelligence');
    }

    public function test_admin_bi_uses_recorded_sales_and_category_snapshots(): void
    {
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create(['email' => 'admin@crystalcaraudio.com']);
        StoreOrder::create([
            'reference' => 'CCA-BI-001', 'customer_name' => 'Amina Wanjiku', 'email' => 'amina@example.test',
            'phone' => '+254700000001', 'address' => 'Nairobi',
            'items' => [['name' => 'Road Radio', 'price' => 5000, 'quantity' => 1, 'category' => 'Car Radio', 'brand' => 'Pioneer']],
            'total' => 4500, 'discount' => 500, 'payment_method' => 'cod', 'payment_status' => 'paid', 'order_status' => 'completed',
        ]);
        StoreOrder::create([
            'reference' => 'CCA-BI-002', 'customer_name' => 'Brian Otieno', 'email' => 'brian@example.test',
            'phone' => '+254700000002', 'address' => 'Nairobi',
            'items' => [['name' => 'Door Speakers', 'price' => 1200, 'quantity' => 2, 'category' => 'Car Speakers', 'brand' => 'JVC']],
            'total' => 2400, 'discount' => 0, 'payment_method' => 'econfirm', 'payment_status' => 'escrow_held', 'order_status' => 'processing',
        ]);

        $this->actingAs($admin)->get(route('admin.bi', ['period' => '30d', 'category' => 'Car Radio']))
            ->assertOk()
            ->assertSee('Gross sales')
            ->assertSee('KES 5,000')
            ->assertSee('KES 4,500')
            ->assertSee('Road Radio')
            ->assertDontSee('Door Speakers')
            ->assertSee('Product unit costs are not recorded.');
    }

    public function test_admin_can_export_filtered_bi_report_as_csv(): void
    {
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $admin = User::factory()->create(['email' => 'admin@crystalcaraudio.com']);

        $this->actingAs($admin)->get(route('admin.bi', [
            'period' => 'custom', 'from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString(), 'export' => 'csv',
        ]))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_regular_customer_cannot_access_admin_dashboard_or_bi(): void
    {
        config(['store.admin_email' => 'admin@crystalcaraudio.com']);
        $customer = User::factory()->create(['email' => 'customer@example.test']);

        $this->actingAs($customer)->get(route('admin.home'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.bi'))->assertForbidden();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category')->nullable()->index();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('stock')->default('In Stock');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->json('items');
            $table->decimal('total', 12, 2);
            $table->string('payment_method');
            $table->string('payment_status')->default('pending_payment');
            $table->string('order_status')->default('pending');
            $table->string('econfirm_transaction_id')->nullable()->index();
            $table->string('econfirm_session_id')->nullable();
            $table->text('econfirm_webhook_secret')->nullable();
            $table->string('merchant_reference')->nullable()->unique();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('econfirm_webhook_events');
        Schema::dropIfExists('catalog_products');
        Schema::dropIfExists('catalog_categories');
    }
};

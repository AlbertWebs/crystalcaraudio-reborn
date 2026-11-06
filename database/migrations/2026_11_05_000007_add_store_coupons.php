<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('code')->unique();
            $table->decimal('percent_off', 5, 2)->default(0);
            $table->boolean('is_active')->default(false);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::table('store_orders', function (Blueprint $table) {
            $table->string('coupon_code')->nullable();
            $table->decimal('discount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'discount']);
        });
        Schema::dropIfExists('catalog_coupons');
    }
};

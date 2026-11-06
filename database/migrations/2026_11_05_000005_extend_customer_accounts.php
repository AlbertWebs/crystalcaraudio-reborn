<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('legacy_customer_id')->nullable()->unique();
        });
        Schema::create('customer_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wishlists');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['legacy_customer_id']);
            $table->dropColumn(['phone', 'address', 'legacy_customer_id']);
        });
    }
};

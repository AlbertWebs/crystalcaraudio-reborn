<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->decimal('original_price', 12, 2)->nullable();
            $table->string('sku')->nullable()->index();
            $table->json('gallery')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'sku', 'gallery', 'specifications', 'rating', 'review_count']);
        });
    }
};

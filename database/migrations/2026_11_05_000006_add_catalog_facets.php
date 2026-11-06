<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->string('legacy_tag_id')->nullable()->index();
            $table->boolean('is_offer')->default(false)->index();
            $table->boolean('is_special')->default(false);
            $table->boolean('trending')->default(false);
        });
        Schema::create('catalog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_tags');
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['legacy_tag_id', 'is_offer', 'is_special', 'trending']);
        });
    }
};

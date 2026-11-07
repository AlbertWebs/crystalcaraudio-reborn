<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 80)->unique();
            $table->string('title', 255)->nullable();
            $table->string('description', 320)->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_seo_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_media_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('recommended_size')->nullable();
            $table->string('type', 20)->default('image');
            $table->string('path');
            $table->timestamps();
        });

        $now = now();
        foreach ($this->assets() as $asset) {
            DB::table('site_media_assets')->insert([...$asset, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_media_assets');
    }

    /** @return list<array<string, string>> */
    private function assets(): array
    {
        return [
            ['asset_key' => 'homepage_combo_background', 'label' => 'Homepage combo showcase background', 'description' => 'Background behind the homepage combo product cards.', 'recommended_size' => '1920 × 1200 px, WebP or JPG', 'type' => 'image', 'path' => 'images/feature-dashboard-night.webp'],
            ['asset_key' => 'homepage_feature_banner', 'label' => 'Built for the moment banner', 'description' => 'Wide image behind the homepage “Built for the moment” section.', 'recommended_size' => '1920 × 900 px, WebP or JPG', 'type' => 'image', 'path' => 'images/feature-dashboard-night.webp'],
            ['asset_key' => 'homepage_setup_background', 'label' => 'Whole setup section background', 'description' => 'Low contrast background art for the home page support cards.', 'recommended_size' => '1600 × 900 px, WebP or JPG', 'type' => 'image', 'path' => 'images/whole-setup/speaker-interior.webp'],
            ['asset_key' => 'footer_background_video', 'label' => 'Footer background video', 'description' => 'Subtle video loop behind the footer content.', 'recommended_size' => '1920 × 1080 px, MP4, under 20 MB', 'type' => 'video', 'path' => 'videos/footer-motion.mp4'],
        ];
    }
};

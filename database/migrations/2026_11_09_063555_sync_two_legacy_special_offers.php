<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('catalog_products')->where('slug', '9-android-radio-with-dash-cam')->update([
            'price' => 35000,
            'original_price' => 40000,
            'is_offer' => true,
        ]);

        DB::table('catalog_products')->where('slug', 'car-reverse-camera-with-clear-vision')->update([
            'price' => 2000,
            'original_price' => 2500,
            'is_offer' => true,
        ]);

        DB::table('catalog_products')->where('slug', 'conjoined-five-seater-car-floor-mats')->update([
            'price' => 2000,
            'original_price' => 2500,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('catalog_products')->where('slug', '9-android-radio-with-dash-cam')->update([
            'price' => 40000,
            'original_price' => null,
            'is_offer' => false,
        ]);

        DB::table('catalog_products')->where('slug', 'car-reverse-camera-with-clear-vision')->update([
            'price' => 2500,
            'original_price' => null,
            'is_offer' => false,
        ]);

        DB::table('catalog_products')->where('slug', 'conjoined-five-seater-car-floor-mats')->update([
            'price' => 2500,
            'original_price' => null,
        ]);
    }
};

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
        DB::table('catalog_products')->where('slug', 'boschmann-equalizer-eqx-75pro')->update([
            'image' => 'product/637-fb_pixels.png',
            'gallery' => json_encode([
                'product/637-fb_pixels.png',
                'product/Boschmann-Equalizer-EQX-75PRO-thumbnail.png',
            ], JSON_THROW_ON_ERROR),
            'legacy_image' => '637-fb_pixels.png',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('catalog_products')->where('slug', 'boschmann-equalizer-eqx-75pro')->update([
            'image' => 'product/Boschmann-Equalizer-EQX-75PRO-thumbnail.png',
            'gallery' => json_encode(['product/Boschmann-Equalizer-EQX-75PRO-thumbnail.png'], JSON_THROW_ON_ERROR),
            'legacy_image' => 'Boschmann-Equalizer-EQX-75PRO-thumbnail.png',
            'updated_at' => now(),
        ]);
    }
};

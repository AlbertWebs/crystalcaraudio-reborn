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
        foreach ($this->categoryArtwork() as $name => $image) {
            DB::table('catalog_categories')
                ->where('name', $name)
                ->where(function ($query): void {
                    $query->whereNull('image')
                        ->orWhere('image', '')
                        ->orWhere('image', 'like', 'categories/%');
                })
                ->update(['image' => $image, 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->categoryArtwork() as $name => $image) {
            DB::table('catalog_categories')->where('name', $name)->where('image', $image)
                ->update(['image' => null, 'updated_at' => now()]);
        }
    }

    /** @return array<string, string> */
    private function categoryArtwork(): array
    {
        return [
            'Car Radio' => 'images/categories/car-radio.webp',
            'Car Speakers' => 'images/categories/car-speakers.webp',
            'Car Booster Amplifiers' => 'images/categories/car-amplifiers.webp',
            'Equalizers' => 'images/categories/equalizers.webp',
            'Car Alarm and Trackers' => 'images/categories/car-security.webp',
            'In-Car Accessories' => 'images/categories/in-car-accessories.webp',
            'Car Audio System' => 'images/categories/car-audio-system.webp',
            'Conversion Fascia Kits' => 'images/categories/fascia-kits.webp',
            'Car Audio Installation' => 'images/categories/car-audio-installation.webp',
        ];
    }
};

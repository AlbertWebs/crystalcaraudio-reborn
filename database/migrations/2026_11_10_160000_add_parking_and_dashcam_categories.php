<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $categories = [
        [
            'name' => 'Parking Sensors',
            'slug' => 'parking-sensors',
            'description' => 'Shop parking sensor kits to help detect obstacles while manoeuvring. Check each product for sensor count, alert type and vehicle fitment details.',
            'image' => 'images/categories/parking-sensors.png',
        ],
        [
            'name' => 'Car Dashcams',
            'slug' => 'car-dashcams',
            'description' => 'Browse car dash cameras for recording your journeys. Compare camera channels, recording features and storage requirements, and confirm installation compatibility for your vehicle.',
            'image' => 'images/categories/car-dashcams.png',
        ],
    ];

    private array $productCategories = [
        'parking-sensors' => [
            '8pc-car-parking-sensors-black',
            'parking-sensors-for-cars-black',
        ],
        'car-dashcams' => [
            '4g-2k-hd-dashcam-with-gps-tracking',
            'kenwood-drv-a201-recording-dashcam',
            'azdome-m550-pro-driving-recorder-3-ch',
            'wifi-3-channel-mini-car-dvr-three-way',
            '3-lens-driving-recorder-car-dash',
            '4k-dual-lense-car-driving-recorder',
            'pioneer-dashcam-vrec-z820dc-2-channel',
            'pioneer-vrec-520dc-2-ch-dashcam',
            'vrec-h320sc-pioneer-full-hd-dashcam',
            'vrec-h120sc-pioneer-dashcam',
        ],
    ];

    public function up(): void
    {
        $nextSortOrder = (int) DB::table('catalog_categories')->max('sort_order');

        foreach ($this->categories as $category) {
            if (! DB::table('catalog_categories')->where('slug', $category['slug'])->exists()) {
                DB::table('catalog_categories')->insert([
                    ...$category,
                    'sort_order' => ++$nextSortOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($this->productCategories as $slug => $productSlugs) {
            $name = collect($this->categories)->firstWhere('slug', $slug)['name'];
            DB::table('catalog_products')->whereIn('slug', $productSlugs)->update([
                'category' => $name,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->productCategories as $slug => $productSlugs) {
            DB::table('catalog_products')->whereIn('slug', $productSlugs)->update([
                'category' => 'In-Car Accessories',
                'updated_at' => now(),
            ]);
        }

        DB::table('catalog_categories')->whereIn('slug', array_column($this->categories, 'slug'))->delete();
    }
};

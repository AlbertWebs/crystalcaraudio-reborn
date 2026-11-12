<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_projects', function (Blueprint $table): void {
            $table->json('related_product_slugs')->nullable();
            $table->json('related_category_slugs')->nullable();
        });

        $taggedProjects = [
            '2' => [
                'products' => ['sony-speakers-xs-gtf6939'],
                'categories' => ['car-radio'],
            ],
            '3' => [
                'products' => [],
                'categories' => ['car-radio', 'led-lights'],
            ],
            '4' => [
                'products' => ['car-radio-xav-ax3200-with-android-aut', 'ts-wx400da-pioneer-underseat-sub'],
                'categories' => ['car-radio', 'car-speakers'],
            ],
            '5' => [
                'products' => [
                    'kenwood-bass-speaker-kfc-w3010',
                    'pioneer-amplifier-booster-gm-e7004',
                    'pioneer-ts-s20-tweeters-200-watts',
                    'kenwood-speakers65-300w-kfc-s1666',
                ],
                'categories' => [],
            ],
        ];

        foreach ($taggedProjects as $legacySourceId => $tags) {
            DB::table('portfolio_projects')
                ->where('legacy_source_id', $legacySourceId)
                ->update([
                    'related_product_slugs' => json_encode($tags['products'], JSON_THROW_ON_ERROR),
                    'related_category_slugs' => json_encode($tags['categories'], JSON_THROW_ON_ERROR),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('portfolio_projects', function (Blueprint $table): void {
            $table->dropColumn(['related_product_slugs', 'related_category_slugs']);
        });
    }
};

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAndroidRadioBrand extends Command
{
    protected $signature = 'catalog:update-android-radio-brand {--apply : Write the changes (requires APP_ENV=production)}';

    protected $description = 'Set generic Android radio products currently branded Others to Android Radios.';

    public function handle(): int
    {
        $connection = DB::connection();
        if ($this->option('apply') && app()->environment() !== 'production') {
            $this->error('Refusing to write: --apply requires APP_ENV=production.');

            return self::FAILURE;
        }

        $categoryExists = DB::table('catalog_categories')->where('name', 'Android Car Radios')->exists();
        if (! $categoryExists) {
            $this->error('Required category "Android Car Radios" is missing; no changes were made.');

            return self::FAILURE;
        }

        $products = DB::table('catalog_products')
            ->whereRaw('LOWER(TRIM(brand)) = ?', ['others'])
            ->whereRaw('LOWER(name) LIKE ? AND LOWER(name) LIKE ?', ['%android%', '%radio%'])
            ->orderBy('id')
            ->get(['id', 'name', 'brand', 'sku', 'category']);

        if ($products->isEmpty()) {
            $this->info('No matching products found.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Product', 'Current brand', 'SKU', 'Current category'], $products->map(fn ($product) => [
            $product->id,
            $product->name,
            $product->brand,
            $product->sku,
            $product->category,
        ]));

        if (! $this->option('apply')) {
            $this->warn("Preview only: {$products->count()} product(s) match. Run with --apply on the production server to update brand and category.");

            return self::SUCCESS;
        }

        $updated = DB::transaction(function () use ($products): int {
            $count = 0;
            foreach ($products as $product) {
                $count += DB::table('catalog_products')
                    ->where('id', $product->id)
                    ->whereRaw('LOWER(TRIM(brand)) = ?', ['others'])
                    ->update([
                        'brand' => 'Android Radios',
                        'category' => 'Android Car Radios',
                        'updated_at' => now(),
                    ]);
            }

            return $count;
        });

        $this->info("Updated {$updated} product(s): brand set to Android Radios and category set to Android Car Radios.");

        return self::SUCCESS;
    }
}

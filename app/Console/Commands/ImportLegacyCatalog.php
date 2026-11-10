<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyCatalog extends Command
{
    protected $signature = 'legacy:import-catalog {--sql= : Path to the legacy SQL dump} {--catalog-only : Refresh catalogue data without re-importing the archive} {--skip-wishlist : Keep the already-imported large legacy wishlist archive untouched}';

    protected $description = 'Import legacy categories and products without changing the original dump.';

    private array $mediaFiles = [];

    public function handle(): int
    {
        $path = $this->option('sql') ?: base_path('../crystal3_crystal.sql');
        if (! is_file($path)) {
            $this->error("SQL dump not found: {$path}");

            return self::FAILURE;
        }
        $counts = ['category' => 0, 'product' => 0];
        foreach (['category', 'product'] as $table) {
            foreach ($this->records($path, $table) as $record) {
                if ($table === 'category') {
                    $slug = trim((string) ($record['slung'] ?? '')) ?: Str::slug($record['cat'] ?? 'category');
                    $categoryImage = trim((string) ($record['image'] ?? ''));
                    DB::table('catalog_categories')->updateOrInsert(['legacy_id' => (string) ($record['id'] ?? '')], [
                        'legacy_id' => (string) ($record['id'] ?? ''),
                        'name' => $record['cat'] ?? 'Category', 'slug' => $slug, 'description' => $record['description'] ?? null,
                        'image' => str_starts_with($categoryImage, 'http') ? $categoryImage : $this->resolveImage($categoryImage),
                        'updated_at' => now(), 'created_at' => $record['created_at'] ?? now(),
                    ]);
                    $counts[$table]++;

                    continue;
                }
                $legacyId = (string) ($record['id'] ?? '');
                if ($legacyId === '') {
                    continue;
                }
                $name = trim((string) ($record['name'] ?? '')) ?: 'Product '.$legacyId;
                $slug = trim((string) ($record['slung'] ?? '')) ?: Str::slug($name.'-'.$legacyId);
                $image = trim((string) ($record['fb_pixels'] ?? ''))
                    ?: trim((string) ($record['thumbnail'] ?? ''))
                    ?: trim((string) ($record['image_one'] ?? ''));
                $storedImage = $this->resolveImage($image);
                $offerImage = $this->resolveImage(trim((string) ($record['offer_banner'] ?? '')));
                $galleryFields = (bool) ($record['offer'] ?? false) ? ['offer_banner'] : [];
                $gallery = collect([
                    ...$galleryFields,
                    'fb_pixels',
                    'thumbnail',
                    'image_one',
                    'image_two',
                    'image_three',
                    'image_four',
                ])
                    ->map(fn ($field) => $this->resolveImage(trim((string) ($record[$field] ?? ''))))
                    ->filter()->unique()->values()->all();
                $specifications = json_decode((string) ($record['extras'] ?? ''), true);
                $price = is_numeric($record['price'] ?? null) ? (float) $record['price'] : (is_numeric($record['price_raw'] ?? null) ? (float) $record['price_raw'] : 0);
                $originalPrice = is_numeric($record['price_raw'] ?? null) && (float) $record['price_raw'] > $price ? (float) $record['price_raw'] : null;
                $category = DB::table('catalog_categories')->where('legacy_id', (string) ($record['cat'] ?? ''))->value('name') ?: ($record['cat'] ?? null);
                DB::table('catalog_products')->updateOrInsert(['legacy_id' => $legacyId], [
                    'name' => $name, 'slug' => $slug, 'category' => $category,
                    'brand' => $record['brand'] ?? null, 'description' => $this->productDescription($record),
                    'image' => $storedImage, 'offer_image' => $offerImage, 'legacy_image' => $image ?: null,
                    'price' => $price, 'original_price' => $originalPrice, 'sku' => $record['code'] ?? null,
                    'gallery' => json_encode($gallery), 'specifications' => json_encode(is_array($specifications) ? $specifications : []),
                    'stock' => $record['stock'] ?? 'In Stock', 'featured' => (bool) ($record['featured'] ?? false),
                    'legacy_tag_id' => isset($record['tag']) ? (string) $record['tag'] : null,
                    'is_offer' => (bool) ($record['offer'] ?? false), 'is_special' => (bool) ($record['special'] ?? false),
                    'trending' => (bool) ($record['trending'] ?? false),
                    'updated_at' => now(), 'created_at' => $record['created_at'] ?? now(),
                ]);
                $counts[$table]++;
            }
            $this->info("Imported {$counts[$table]} {$table} records.");
        }

        foreach ($this->records($path, 'tags') as $record) {
            $slug = trim((string) ($record['slung'] ?? '')) ?: Str::slug($record['title'] ?? 'tag');
            DB::table('catalog_tags')->updateOrInsert(['legacy_id' => (string) ($record['id'] ?? '')], [
                'name' => $record['title'] ?? 'Tag', 'slug' => $slug, 'description' => $record['content'] ?? null,
                'updated_at' => now(), 'created_at' => $record['created_at'] ?? now(),
            ]);
        }
        $reviewTotals = [];
        foreach ($this->records($path, 'reviews') as $record) {
            $productId = (string) ($record['product_id'] ?? '');
            $rating = (float) ($record['rating'] ?? 0);
            if ($productId !== '' && $rating > 0 && $rating <= 100) {
                $reviewTotals[$productId]['sum'] = ($reviewTotals[$productId]['sum'] ?? 0) + $rating;
                $reviewTotals[$productId]['count'] = ($reviewTotals[$productId]['count'] ?? 0) + 1;
            }
        }
        foreach ($reviewTotals as $productId => $total) {
            DB::table('catalog_products')->where('legacy_id', $productId)->update([
                'rating' => round($total['sum'] / $total['count'] / 20, 2), 'review_count' => $total['count'],
            ]);
        }
        foreach ($this->records($path, 'coupon_codes') as $record) {
            $code = trim((string) ($record['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            DB::table('catalog_coupons')->updateOrInsert(['code' => $code], [
                'legacy_id' => (string) ($record['id'] ?? ''), 'title' => $record['title'] ?? null,
                'percent_off' => is_numeric($record['value'] ?? null) ? $record['value'] : 0,
                'is_active' => (string) ($record['status'] ?? '') === '1', 'expires_at' => $record['expired_at'] ?? null,
                'updated_at' => now(), 'created_at' => $record['created_at'] ?? now(),
            ]);
        }

        if ($this->option('catalog-only')) {
            return self::SUCCESS;
        }
        $archiveTables = ['users', 'orders', 'orders_products', 'invoices', 'payments', 'mobile_payments', 'lnmo_api_response', 'mpesa_stk_requests', 'mpesa_stk_callbacks', 'mpesa_c2b_callbacks', 'mpesa_bulk_payment_requests', 'mpesa_bulk_payment_responses', 'transaction_status', 'reverse_transaction', 'b2b_api_response', 'b2c_api_response', 'reviews', 'wishlist', 'wishlists', 'coupons', 'coupon_codes', 'brands', 'tags', 'about', 'banners', 'blogs', 'copyright', 'delivery', 'portfolio', 'privacy', 'terms', 'services', 'sitesettings', 'seosettings', 'slider', 'sliders', 'special_offers', 'offers', 'team', 'testimonial', 'videos', 'messages', 'notifications', 'comments'];
        foreach ($archiveTables as $table) {
            if ($this->option('skip-wishlist') && $table === 'wishlist') {
                continue;
            }
            $count = 0;
            $batch = [];
            foreach ($this->records($path, $table) as $record) {
                if ($table === 'users' && filter_var($record['email'] ?? null, FILTER_VALIDATE_EMAIL) && filled($record['password'] ?? null)) {
                    DB::table('users')->updateOrInsert(['email' => $record['email']], [
                        'name' => $record['name'] ?: 'Crystal customer', 'password' => $record['password'],
                        'phone' => $record['mobile'] ?? null, 'address' => $record['address'] ?? null,
                        'legacy_customer_id' => (string) ($record['id'] ?? ''), 'email_verified_at' => $record['email_verified_at'] ?? null,
                        'updated_at' => now(), 'created_at' => $record['created_at'] ?? now(),
                    ]);
                }
                $payload = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $sourceId = (string) ($record['id'] ?? hash('sha256', $payload));
                $batch[] = ['source_table' => $table, 'source_id' => $sourceId,
                    'payload' => Crypt::encryptString($payload), 'updated_at' => now(),
                    'created_at' => $record['created_at'] ?? now()];
                $count++;
                if (count($batch) >= 500) {
                    DB::table('legacy_archive_records')->upsert($batch, ['source_table', 'source_id'], ['payload', 'updated_at']);
                    $batch = [];
                }
            }
            if ($batch !== []) {
                DB::table('legacy_archive_records')->upsert($batch, ['source_table', 'source_id'], ['payload', 'updated_at']);
            }
            $this->info("Archived {$count} {$table} records with original fields.");
        }

        return self::SUCCESS;
    }

    private function records(string $path, string $table): \Generator
    {
        $file = new \SplFileObject($path, 'rb');
        $target = false;
        $header = '';
        $columns = [];
        $row = '';
        $inside = false;
        $quoted = false;
        $escaped = false;
        while (! $file->eof()) {
            $line = $file->fgets();
            if (! $target) {
                if ($header === '' && str_starts_with(ltrim($line), 'INSERT INTO `'.$table.'` (')) {
                    $header = $line;
                } elseif ($header !== '') {
                    $header .= $line;
                }
                if ($header !== '' && preg_match('/\((.*?)\)\s+VALUES\s*$/s', trim($header), $match)) {
                    $columns = array_map(fn ($v) => trim($v, " `\t\r\n"), explode(',', $match[1]));
                    $target = true;
                    $header = '';
                }

                continue;
            }
            $length = strlen($line);
            for ($i = 0; $i < $length; $i++) {
                $char = $line[$i];
                if (! $inside) {
                    if ($char === '(') {
                        $inside = true;
                        $row = '';
                        $quoted = false;
                        $escaped = false;
                    } elseif ($char === ';') {
                        $target = false;
                        break;
                    }

                    continue;
                }
                if ($escaped) {
                    $row .= $char;
                    $escaped = false;

                    continue;
                }
                if ($char === '\\' && $quoted) {
                    $row .= $char;
                    $escaped = true;

                    continue;
                }
                if ($char === "'") {
                    $quoted = ! $quoted;
                    $row .= $char;

                    continue;
                }
                if ($char === ')' && ! $quoted) {
                    $values = $this->parseValues($row);
                    if (count($values) === count($columns)) {
                        yield array_combine($columns, $values);
                    }
                    $inside = false;
                    $row = '';

                    continue;
                }
                $row .= $char;
            }
        }
    }

    private function parseValues(string $row): array
    {
        $tokens = [];
        $token = '';
        $quoted = false;
        $escaped = false;
        $length = strlen($row);
        for ($i = 0; $i < $length; $i++) {
            $char = $row[$i];
            if ($escaped) {
                $token .= $char;
                $escaped = false;

                continue;
            }
            if ($char === '\\' && $quoted) {
                $escaped = true;

                continue;
            }
            if ($char === "'") {
                $quoted = ! $quoted;

                continue;
            }
            if ($char === ',' && ! $quoted) {
                $tokens[] = $this->decode(trim($token));
                $token = '';

                continue;
            }
            $token .= $char;
        }
        $tokens[] = $this->decode(trim($token));

        return $tokens;
    }

    private function decode(string $value): mixed
    {
        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return str_replace(["\\'", '\\"', '\\\\', '\\n', '\\r'], ["'", '"', '\\', "\n", "\r"], $value);
    }

    private function resolveImage(string $filename): ?string
    {
        if ($filename === '') {
            return null;
        }
        if ($this->mediaFiles === []) {
            foreach (['product', 'productss', 'categories', 'brands', 'slider', 'sliders', 'banners'] as $directory) {
                foreach (glob(public_path('uploads/'.$directory.'/*')) ?: [] as $file) {
                    if (is_file($file)) {
                        $this->mediaFiles[strtolower(basename($file))] ??= $directory.'/'.basename($file);
                    }
                }
            }
            foreach (glob(public_path('uploads/*')) ?: [] as $file) {
                if (is_file($file)) {
                    $this->mediaFiles[strtolower(basename($file))] ??= basename($file);
                }
            }
        }
        $basename = strtolower(basename(str_replace('\\', '/', $filename)));

        return $this->mediaFiles[$basename]
            ?? $this->mediaFiles[str_replace(':', '_', $basename)]
            ?? null;
    }

    private function productDescription(array $record): ?string
    {
        foreach (['content', 'full'] as $field) {
            $description = trim((string) ($record[$field] ?? ''));
            if ($description !== '' && $description !== '0') {
                return $description;
            }
        }

        return null;
    }
}

<?php

namespace App\Services;

use App\Models\CatalogProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MerchantFeedAudit
{
    /** @return Collection<int, array{product: CatalogProduct, reasons: list<string>}> */
    public function productsNeedingUpdates(): Collection
    {
        return $this->auditedProducts()->filter(fn (array $item): bool => $item['reasons'] !== [])->values();
    }

    /** @return Collection<int, CatalogProduct> */
    public function eligibleProducts(): Collection
    {
        return $this->auditedProducts()
            ->filter(fn (array $item): bool => $item['reasons'] === [])
            ->pluck('product')
            ->values();
    }

    /** @return Collection<int, array{product: CatalogProduct, reasons: list<string>}> */
    private function auditedProducts(): Collection
    {
        return CatalogProduct::query()->orderBy('name')->get()
            ->map(fn (CatalogProduct $product): array => [
                'product' => $product,
                'reasons' => $this->exclusionReasons($product),
            ])
            ->values();
    }

    /** @return list<string> */
    private function exclusionReasons(CatalogProduct $product): array
    {
        $reasons = [];
        $title = $this->plainText($product->name);
        $description = $this->plainText($product->description);
        $brand = trim((string) $product->brand);
        $normalizedBrand = Str::lower($brand);
        $category = trim((string) $product->category);
        $stock = Str::lower(trim((string) $product->stock));

        if ($title === '') {
            $reasons[] = 'Product title is missing.';
        } elseif (Str::length($title) > 150) {
            $reasons[] = 'Product title exceeds Google’s 150-character limit.';
        }

        if ($description === '') {
            $reasons[] = 'Product description is missing.';
        } elseif (Str::length($description) > 5000) {
            $reasons[] = 'Product description exceeds Google’s 5,000-character limit.';
        }

        if ((float) $product->price <= 0) {
            $reasons[] = 'Price must be greater than zero.';
        }

        if (trim((string) $product->image) === '') {
            $reasons[] = 'Main product image is missing.';
        }

        if ($category === '') {
            $reasons[] = 'Product category is missing.';
        }

        if ($brand === '' || in_array($normalizedBrand, ['other', 'others', 'unknown', 'n/a', 'na', 'none'], true)) {
            $reasons[] = $brand === '' ? 'Brand is missing.' : 'Brand is set to “'.$brand.'”; enter the actual manufacturer brand.';
        }

        if (trim((string) ($product->mpn ?: ($this->hasManufacturerBrand($brand) ? $product->sku : ''))) === '' && trim((string) $product->gtin) === '') {
            $reasons[] = 'No manufacturer identifier is recorded; add the exact GTIN or manufacturer part number (MPN).';
        }

        if (Str::contains($stock, ['pre-order', 'preorder'])) {
            $reasons[] = 'Stock is preorder, but no availability date is recorded.';
        } elseif ($product->stock_quantity === null && ! in_array($stock, ['in stock', 'out of stock'], true)) {
            $reasons[] = 'Stock status “'.trim((string) $product->stock).'” is not recognized.';
        } elseif ($product->stock_quantity !== null && (int) $product->stock_quantity < 0) {
            $reasons[] = 'Stock quantity cannot be negative.';
        }

        return $reasons;
    }

    private function hasManufacturerBrand(string $brand): bool
    {
        return $brand !== '' && ! in_array(Str::lower(trim($brand)), ['other', 'others', 'unknown', 'n/a', 'na', 'none'], true);
    }

    private function plainText(?string $value): string
    {
        $plainText = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = preg_replace('/(?:rn){2,}/i', ' ', $plainText) ?? $plainText;
        $plainText = preg_replace('/(?<![A-Za-z])(?:rn)+(?![A-Za-z])/i', ' ', $plainText) ?? $plainText;
        $plainText = preg_replace('/\s+/u', ' ', $plainText) ?? $plainText;

        return trim($plainText);
    }
}

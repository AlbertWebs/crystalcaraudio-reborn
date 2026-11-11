<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class AdminCatalogExportController extends Controller
{
    private const MERCHANT_BASE_URL = 'https://crystalcaraudio.com';

    private const CATALOGUE_COLUMNS = [
        'id' => 'Product ID',
        'sku' => 'SKU',
        'title' => 'Product name',
        'brand' => 'Brand',
        'category' => 'Category',
        'description' => 'Description',
        'price' => 'Price (KES)',
        'original_price' => 'Previous price (KES)',
        'stock' => 'Stock status',
        'stock_quantity' => 'Stock quantity',
        'product_url' => 'Product URL',
        'image_url' => 'Main image URL',
    ];

    public function index()
    {
        $catalogue = CatalogProduct::query()->orderBy('name')->get();
        $excluded = $catalogue
            ->map(fn (CatalogProduct $product): array => [
                'product' => $product,
                'reasons' => $this->merchantExclusionReasons($product),
            ])
            ->filter(fn (array $item): bool => $item['reasons'] !== [])
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage('needs_updates_page');
        $perPage = 25;
        $excludedProducts = new LengthAwarePaginator(
            $excluded->forPage($page, $perPage)->values(),
            $excluded->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'needs_updates_page',
            ],
        );

        return view('admin.exports', [
            'totalProducts' => $catalogue->count(),
            'merchantEligible' => $catalogue->count() - $excluded->count(),
            'merchantSkipped' => $excluded->count(),
            'excludedProducts' => $excludedProducts,
        ]);
    }

    public function download(string $format)
    {
        abort_unless(in_array($format, ['merchant', 'csv', 'excel'], true), 404);

        if ($format === 'merchant') {
            return $this->downloadMerchantFeed();
        }

        $rows = $this->catalogueRows();

        return $format === 'excel'
            ? $this->downloadExcel($rows)
            : $this->downloadCatalogueCsv($rows);
    }

    private function downloadMerchantFeed()
    {
        $headers = [
            'id', 'title', 'description', 'link', 'image_link', 'availability', 'condition', 'price', 'brand', 'mpn', 'product_type', 'sale_price',
        ];
        $products = $this->merchantProducts();

        return response()->streamDownload(function () use ($headers, $products): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($products as $product) {
                $price = (float) $product->price;
                $regularPrice = (float) ($product->original_price ?? 0);
                $hasSalePrice = $regularPrice > $price;
                $row = [
                    (string) $product->id,
                    $this->plainText($product->name),
                    $this->plainText($product->description),
                    $this->productUrl($product->slug),
                    $this->imageUrl($product->image),
                    $this->merchantAvailability($product),
                    'new',
                    number_format($hasSalePrice ? $regularPrice : $price, 2, '.', '').' KES',
                    trim((string) $product->brand),
                    trim((string) $product->sku),
                    trim((string) $product->category),
                    $hasSalePrice ? number_format($price, 2, '.', '').' KES' : '',
                ];

                fputcsv($output, array_map($this->csvSafeCell(...), $row));
            }

            fclose($output);
        }, 'google-merchant-products.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function downloadCatalogueCsv(array $rows)
    {
        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_values(self::CATALOGUE_COLUMNS));

            foreach ($rows as $row) {
                fputcsv($output, array_map($this->csvSafeCell(...), array_values($row)));
            }

            fclose($output);
        }, 'crystal-car-audio-catalogue.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function downloadExcel(array $rows)
    {
        abort_unless(class_exists(ZipArchive::class), 501, 'Excel export requires PHP Zip support.');

        $temporaryFile = tempnam(sys_get_temp_dir(), 'cca-catalogue-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Could not create a temporary Excel export.');
        }

        $zip = new ZipArchive;
        if ($zip->open($temporaryFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($temporaryFile);
            throw new RuntimeException('Could not create the Excel workbook.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        return response()->download($temporaryFile, 'crystal-car-audio-catalogue.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, private',
        ])->deleteFileAfterSend(true);
    }

    private function merchantProducts(): Collection
    {
        return CatalogProduct::query()
            ->orderBy('id')
            ->get()
            ->filter(fn (CatalogProduct $product): bool => $this->merchantExclusionReasons($product) === [])
            ->values();
    }

    private function merchantExclusionReasons(CatalogProduct $product): array
    {
        $reasons = [];
        $title = $this->plainText($product->name);
        $description = $this->plainText($product->description);
        $brand = trim((string) $product->brand);
        $normalizedBrand = Str::lower($brand);
        $sku = trim((string) $product->sku);
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

        if ($brand === '') {
            $reasons[] = 'Brand is missing.';
        } elseif (in_array($normalizedBrand, ['other', 'others', 'unknown', 'n/a', 'na', 'none'], true)) {
            $reasons[] = 'Brand is set to “'.$brand.'”; enter the actual manufacturer brand.';
        }

        if ($sku === '') {
            $reasons[] = 'Manufacturer part number is missing; add the product code (exported as MPN).';
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

    private function catalogueRows(): array
    {
        return CatalogProduct::query()->orderBy('id')->get()->map(function (CatalogProduct $product): array {
            return [
                'id' => (string) $product->id,
                'sku' => (string) ($product->sku ?? ''),
                'title' => $this->plainText($product->name),
                'brand' => trim((string) $product->brand),
                'category' => trim((string) $product->category),
                'description' => $this->plainText($product->description),
                'price' => (float) $product->price,
                'original_price' => $product->original_price === null ? '' : (float) $product->original_price,
                'stock' => (string) $product->stock,
                'stock_quantity' => $product->stock_quantity === null ? '' : (int) $product->stock_quantity,
                'product_url' => $this->productUrl($product->slug),
                'image_url' => $this->imageUrl($product->image),
            ];
        })->all();
    }

    private function imageUrl(?string $image): string
    {
        if (! $image) {
            return '';
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        if (config('filesystems.disks.s3.bucket')) {
            try {
                return Storage::disk('s3')->url('uploads/'.ltrim(rawurldecode($image), '/'));
            } catch (Throwable) {
                // Fall back to the site's local public upload path when S3 is unavailable.
            }
        }

        return self::MERCHANT_BASE_URL.'/uploads/'.ltrim($image, '/');
    }

    private function productUrl(string $slug): string
    {
        return self::MERCHANT_BASE_URL.'/product/'.rawurlencode($slug);
    }

    private function merchantAvailability(CatalogProduct $product): string
    {
        $stock = Str::lower(trim((string) $product->stock));

        if (Str::contains($stock, ['pre-order', 'preorder'])) {
            return 'preorder';
        }

        if ($product->stock_quantity !== null) {
            return (int) $product->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
        }

        return Str::contains($stock, 'out') ? 'out_of_stock' : 'in_stock';
    }

    private function plainText(?string $value): string
    {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function csvSafeCell(mixed $value): string|float|int
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_match('/^[\s]*[=+@\-]/u', $value) === 1 ? "'".$value : $value;
    }

    private function worksheetXml(array $rows): string
    {
        $headers = array_values(self::CATALOGUE_COLUMNS);
        $xmlRows = [];
        $xmlRows[] = $this->worksheetRow(1, $headers);

        foreach ($rows as $index => $row) {
            $xmlRows[] = $this->worksheetRow($index + 2, array_values($row));
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheetData>'.
            implode('', $xmlRows).'</sheetData></worksheet>';
    }

    private function worksheetRow(int $rowNumber, array $values): string
    {
        $cells = '';
        foreach ($values as $index => $value) {
            $reference = $this->columnName($index + 1).$rowNumber;
            $numericColumn = in_array($index, [0, 6, 7, 9], true) && is_numeric($value) && $value !== '';

            if ($numericColumn) {
                $cells .= '<c r="'.$reference.'" t="n"><v>'.(string) $value.'</v></c>';

                continue;
            }

            $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $cells .= '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>';
        }

        return '<row r="'.$rowNumber.'">'.$cells.'</row>';
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $name = chr(65 + $remainder).$name;
            $number = intdiv($number - 1, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
            '<Default Extension="xml" ContentType="application/xml"/>'.
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'.
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'.
            '</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'.
            '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<sheets><sheet name="Product Catalogue" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'.
            '</Relationships>';
    }
}

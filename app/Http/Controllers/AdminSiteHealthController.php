<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Services\MerchantFeedAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSiteHealthController extends Controller
{
    public function index(MerchantFeedAudit $audit): View
    {
        $productsNeedingUpdates = $audit->productsNeedingUpdates()->count();
        $zeroPriceProducts = CatalogProduct::query()->where('price', '<=', 0)->count();
        $outOfStockProducts = CatalogProduct::query()->where(function ($query): void {
            $query->where('stock_quantity', 0)->orWhereRaw('LOWER(stock) = ?', ['out of stock']);
        })->count();
        $lowStockProducts = CatalogProduct::query()->whereNotNull('stock_quantity')
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();
        $uncategorizedProducts = CatalogProduct::query()
            ->where(function ($query): void {
                $query->whereNull('category')->orWhereRaw("TRIM(category) = ''");
            })->count();
        $seoSettings = DB::table('page_seo_settings')->get()->keyBy('page_key');
        $seoPageKeys = ['shop_by_category', 'shop_by_brand', 'category_detail', 'brand_detail', 'portfolio'];
        $emptySeoPages = collect($seoPageKeys)->filter(function (string $pageKey) use ($seoSettings): bool {
            $setting = $seoSettings->get($pageKey);

            return $setting === null || trim((string) $setting->title) === '' || trim((string) $setting->description) === '';
        })->count();
        $mediaAssets = DB::table('site_media_assets')->get()->keyBy('asset_key');
        $mediaAssetKeys = ['homepage_combo_background', 'homepage_feature_banner', 'homepage_setup_background', 'footer_background_video'];
        $emptyMediaAssets = collect($mediaAssetKeys)->filter(function (string $assetKey) use ($mediaAssets): bool {
            return trim((string) ($mediaAssets->get($assetKey)?->path ?? '')) === '';
        })->count();
        $pendingOrders = DB::table('store_orders')->where('order_status', 'pending')->count();
        $pendingInstallations = DB::table('installation_bookings')->where('status', 'pending')->count();
        $databaseConnected = false;

        try {
            DB::select('SELECT 1');
            $databaseConnected = true;
        } catch (\Throwable) {
            $databaseConnected = false;
        }

        $sections = [
            $this->section('Product feed', $productsNeedingUpdates, 'products need updates', 'admin.products-needing-updates', 'Review product issues', 'manage-store-products'),
            $this->section('Pricing', $zeroPriceProducts, 'products are priced at KES 0 or below', 'admin.home', 'Review zero pricing', 'manage-store-products', '#inventory'),
            $this->section('Inventory', $outOfStockProducts + $lowStockProducts, 'products are low or out of stock', 'admin.inventory', 'Update stock statuses', 'manage-store-inventory'),
            $this->section('Categories', $uncategorizedProducts, 'products have no category', 'admin.categories', 'Update categories', 'manage-store-products'),
            $this->section('SEO metadata', $emptySeoPages, 'pages have incomplete saved metadata', 'admin.seo', 'Update SEO details', 'manage-store-products'),
            $this->section('Media assets', $emptyMediaAssets, 'assets have no file configured', 'admin.media', 'Update site media', 'manage-store-products'),
            $this->section('Orders', $pendingOrders, 'orders are awaiting processing', 'admin.orders', 'Update order statuses', 'manage-store-orders'),
            $this->section('Installation bookings', $pendingInstallations, 'bookings are awaiting confirmation', 'admin.installations', 'Update booking statuses', 'manage-installations'),
        ];

        return view('admin.site-health', [
            'sections' => $sections,
            'healthySections' => collect($sections)->where('status', 'healthy')->count() + (int) $databaseConnected,
            'attentionSections' => collect($sections)->where('status', 'attention')->count() + (int) ! $databaseConnected,
            'databaseConnected' => $databaseConnected,
            'categoryCount' => DB::table('catalog_categories')->count(),
            'productCount' => CatalogProduct::query()->count(),
        ]);
    }

    /** @return array{title: string, count: int, detail: string, status: string, status_label: string, route: string, action: string, ability: string, fragment: string} */
    private function section(string $title, int $count, string $detail, string $route, string $action, string $ability, string $fragment = ''): array
    {
        return [
            'title' => $title,
            'count' => $count,
            'detail' => $detail,
            'status' => $count > 0 ? 'attention' : 'healthy',
            'status_label' => $count > 0 ? 'Needs attention' : 'Healthy',
            'route' => $route,
            'action' => $action,
            'ability' => $ability,
            'fragment' => $fragment,
        ];
    }
}

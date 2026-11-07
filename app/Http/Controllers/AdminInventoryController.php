<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminInventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $stockFilter = $request->string('stock')->toString();
        $products = CatalogProduct::query()
            ->when($search !== '', fn ($query) => $query->where(function ($products) use ($search): void {
                $products->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%');
            }))
            ->when($stockFilter === 'low', fn ($query) => $query->whereNotNull('stock_quantity')->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->when($stockFilter === 'out', fn ($query) => $query->where('stock_quantity', 0))
            ->when($stockFilter === 'untracked', fn ($query) => $query->whereNull('stock_quantity'))
            ->orderByRaw('CASE WHEN stock_quantity IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN stock_quantity <= low_stock_threshold THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $movements = DB::table('inventory_movements')
            ->leftJoin('catalog_products', 'catalog_products.id', '=', 'inventory_movements.product_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_movements.created_by')
            ->select(['inventory_movements.*', 'catalog_products.name as product_name', 'users.name as admin_name'])
            ->latest('inventory_movements.created_at')
            ->limit(12)
            ->get();

        $trackedProducts = CatalogProduct::query()->whereNotNull('stock_quantity');
        $inventoryValue = CatalogProduct::query()->whereNotNull('stock_quantity')->whereNotNull('cost_price')
            ->selectRaw('SUM(stock_quantity * cost_price) as value')->value('value');

        return view('admin.inventory', [
            'products' => $products,
            'movements' => $movements,
            'search' => $search,
            'stockFilter' => $stockFilter,
            'trackedProductsCount' => (clone $trackedProducts)->count(),
            'lowStockCount' => (clone $trackedProducts)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'outOfStockCount' => (clone $trackedProducts)->where('stock_quantity', 0)->count(),
            'untrackedProductsCount' => CatalogProduct::query()->whereNull('stock_quantity')->count(),
            'pricedProductsCount' => CatalogProduct::query()->whereNotNull('stock_quantity')->whereNotNull('cost_price')->count(),
            'inventoryValue' => $inventoryValue === null ? null : (float) $inventoryValue,
        ]);
    }

    public function update(Request $request, CatalogProduct $product): RedirectResponse
    {
        $data = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason' => ['required', 'string', 'max:190'],
        ]);

        DB::transaction(function () use ($data, $product, $request): void {
            $lockedProduct = CatalogProduct::query()->lockForUpdate()->findOrFail($product->id);
            $before = $lockedProduct->stock_quantity === null ? null : (int) $lockedProduct->stock_quantity;
            $after = (int) $data['stock_quantity'];
            $change = $before === null ? $after : $after - $before;

            $lockedProduct->update([
                'stock_quantity' => $after,
                'stock' => $after > 0 ? 'In Stock' : 'Out of Stock',
            ]);

            DB::table('inventory_movements')->insert([
                'product_id' => $lockedProduct->id,
                'created_by' => $request->user()->id,
                'quantity_change' => $change,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => trim($data['reason']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('admin_activity_logs')->insert([
                'user_id' => $request->user()->id,
                'action' => 'inventory.adjusted',
                'subject_type' => 'catalog_product',
                'subject_id' => (string) $lockedProduct->id,
                'summary' => 'Updated stock for '.$lockedProduct->name.' from '.($before === null ? 'untracked' : $before).' to '.$after.'.',
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Stock quantity updated and recorded in inventory history.');
    }
}

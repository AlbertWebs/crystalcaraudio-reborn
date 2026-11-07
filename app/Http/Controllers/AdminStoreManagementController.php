<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminStoreManagementController extends Controller
{
    public function categories(): View
    {
        $categories = DB::table('catalog_categories')
            ->leftJoin('catalog_products', 'catalog_products.category', '=', 'catalog_categories.name')
            ->select('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order', DB::raw('COUNT(catalog_products.id) as product_count'))
            ->groupBy('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order')
            ->orderByRaw('CASE WHEN catalog_categories.sort_order = 0 THEN 1 ELSE 0 END')
            ->orderBy('catalog_categories.sort_order')->orderBy('catalog_categories.name')->get();

        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190', Rule::unique('catalog_categories', 'name')],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash', Rule::unique('catalog_categories', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $slug = Str::slug($data['slug'] ?: $data['name']) ?: 'category';
        $baseSlug = $slug;
        $suffix = 2;
        while (DB::table('catalog_categories')->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        DB::transaction(function () use ($data, $slug, $request): void {
            $categoryId = DB::table('catalog_categories')->insertGetId([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'sort_order' => ((int) DB::table('catalog_categories')->max('sort_order')) + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('admin_activity_logs')->insert([
                'user_id' => $request->user()->id,
                'action' => 'category.created',
                'subject_type' => 'category',
                'subject_id' => (string) $categoryId,
                'summary' => 'Added '.$data['name'].' to catalogue categories.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('admin.categories')->with('success', $data['name'].' was added to catalogue categories.');
    }

    public function brands(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')
            ->when($search !== '', fn ($query) => $query->where('brand', 'like', '%'.$search.'%'))
            ->select('brand', DB::raw('COUNT(*) as product_count'), DB::raw('SUM(CASE WHEN price = 0 THEN 1 ELSE 0 END) as zero_price_count'))
            ->groupBy('brand')->orderBy('brand')->paginate(24)->withQueryString();

        return view('admin.brands', compact('brands', 'search'));
    }

    public function orders(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $payment = $request->string('payment')->toString();
        $orders = StoreOrder::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('reference', 'like', '%'.$search.'%')->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%');
            }))
            ->when($status !== '', fn ($query) => $query->where('order_status', $status))
            ->when($payment !== '', fn ($query) => $query->where('payment_status', $payment))
            ->latest('created_at')->paginate(20)->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'payment' => $payment,
            'orderCount' => StoreOrder::query()->count(),
            'pendingCount' => StoreOrder::query()->where('order_status', 'pending')->count(),
            'processingCount' => StoreOrder::query()->whereIn('order_status', ['processing', 'shipped'])->count(),
            'paidValue' => StoreOrder::query()->whereIn('payment_status', ['paid', 'escrow_held'])->sum('total'),
        ]);
    }

    public function customers(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $customers = User::query()->whereNull('admin_role')->where('email', '!=', config('store.admin_email'))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->select('users.*')
            ->selectSub(DB::table('store_orders')->selectRaw('COUNT(*)')->whereColumn('store_orders.email', 'users.email'), 'orders_count')
            ->selectSub(DB::table('store_orders')->selectRaw('MAX(created_at)')->whereColumn('store_orders.email', 'users.email'), 'last_order_at')
            ->latest('created_at')->paginate(25)->withQueryString();

        return view('admin.customers', [
            'customers' => $customers,
            'search' => $search,
            'customerCount' => User::query()->whereNull('admin_role')->where('email', '!=', config('store.admin_email'))->count(),
            'orderingCustomerCount' => DB::table('store_orders')->distinct('email')->count('email'),
            'guestOrderCount' => StoreOrder::query()->whereNotIn('email', User::query()->select('email'))->count(),
        ]);
    }
}

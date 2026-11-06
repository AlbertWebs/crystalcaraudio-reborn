<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\StoreOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function loginForm() { return view('admin.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Those details did not match an account.'])->onlyInput('email');
        $request->session()->regenerate();
        if (! $request->user()->can('manage-store')) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); abort(403); }
        return redirect()->intended(route('admin.home'));
    }

    public function logout(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function index()
    {
        $legacyUsers = DB::table('legacy_archive_records')->where('source_table', 'users')->get(['source_id', 'payload']);
        $customerNames = $legacyUsers->mapWithKeys(function ($user) {
            try { $data = json_decode(Crypt::decryptString($user->payload), true); return [(string) $user->source_id => $data['name'] ?? 'Legacy customer']; }
            catch (\Throwable) { return [(string) $user->source_id => 'Legacy customer']; }
        });
        $legacyOrders = DB::table('legacy_archive_records')->where('source_table', 'orders')->orderByDesc('CAST(source_id AS INTEGER)')->get()->map(function ($record) use ($customerNames) {
            $order = json_decode(Crypt::decryptString($record->payload), true) ?: [];
            $order['customer_name'] = $customerNames[(string) ($order['user_id'] ?? '')] ?? 'Legacy customer';
            return (object) $order;
        });
        return view('admin.index', [
            'productsCount' => CatalogProduct::count(), 'ordersCount' => StoreOrder::count(),
            'pendingCount' => StoreOrder::where('payment_status', 'pending_payment')->count(),
            'categories' => DB::table('catalog_categories')->leftJoin('catalog_products', 'catalog_products.category', '=', 'catalog_categories.name')
                ->select('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order', DB::raw('COUNT(catalog_products.id) as product_count'))
                ->groupBy('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order')
                ->orderByRaw('CASE WHEN catalog_categories.sort_order = 0 THEN 1 ELSE 0 END')->orderBy('catalog_categories.sort_order')->orderBy('catalog_categories.name')->get(),
            'orders' => StoreOrder::latest()->paginate(15),
            'products' => CatalogProduct::orderByDesc('updated_at')->paginate(15, ['*'], 'products_page'),
            'legacyCustomersCount' => $legacyUsers->count(), 'legacyOrders' => $legacyOrders,
        ]);
    }

    public function updateCategoryOrder(Request $request)
    {
        $categoryCount = DB::table('catalog_categories')->count();
        $data = $request->validate([
            'categories' => ['required','array','size:'.$categoryCount],
            'categories.*.id' => ['required','integer','distinct','exists:catalog_categories,id'],
            'categories.*.position' => ['required','integer','min:1','max:'.$categoryCount,'distinct'],
        ]);
        $expectedIds = DB::table('catalog_categories')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values();
        $submittedIds = collect($data['categories'])->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        if ($expectedIds->sort()->values()->all() !== $submittedIds->all()) {
            return back()->withErrors(['categories' => 'Refresh the page and arrange every category before saving.']);
        }

        DB::transaction(function () use ($data) {
            foreach ($data['categories'] as $category) {
                DB::table('catalog_categories')->where('id', $category['id'])->update(['sort_order' => $category['position'], 'updated_at' => now()]);
            }
        });
        return redirect()->route('admin.home')->with('success', 'Category order saved across the storefront.');
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:190'], 'slug' => ['nullable','string','max:190'],
            'category' => ['nullable','string','max:190'], 'brand' => ['nullable','string','max:190'],
            'price' => ['required','numeric','min:0'], 'stock' => ['required','string','max:100'],
            'description' => ['nullable','string'], 'image' => ['nullable','image','max:5120'],
        ]);
        $slug = $data['slug'] ?: Str::slug($data['name']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = Str::uuid().'.'.$file->extension();
            $file->move(public_path('uploads/product'), $name);
            $data['image'] = 'product/'.$name;
        }
        unset($data['slug']);
        CatalogProduct::create($data + ['slug' => $slug]);
        return redirect()->route('admin.home')->with('success', 'Product added to the catalogue.');
    }

    public function updateOrder(Request $request, int $id)
    {
        $data = $request->validate([
            'order_status' => ['required','in:pending,processing,shipped,completed,cancelled'],
            'payment_status' => ['required','in:pending_payment,paid,escrow_held,refunded'],
        ]);
        StoreOrder::findOrFail($id)->update($data);
        return redirect()->route('admin.home')->with('success', 'Order status updated.');
    }
}

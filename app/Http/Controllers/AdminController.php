<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\StoreOrder;
use App\Models\User;
use App\Services\AdminEmailTwoFactor;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request, AdminEmailTwoFactor $twoFactor)
    {
        $twoFactor->forgetChallenge($request);
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those details did not match an account.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        if (! $request->user()->can('manage-store')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403);
        }

        $user = $request->user();
        if ($user->admin_email_2fa_enabled) {
            $remember = $request->boolean('remember');
            Auth::logout();
            $request->session()->regenerate();

            if (! $twoFactor->sendCode($request, $user, $remember)) {
                return redirect()->route('admin.login')->withErrors(['email' => 'We could not send your verification code. Please try again shortly.'])->onlyInput('email');
            }

            return redirect()->route('admin.two-factor');
        }

        return redirect()->intended(route('admin.home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function index()
    {
        $canViewOrderDetails = request()->user()->can('manage-store-orders');
        $legacyUsers = $canViewOrderDetails
            ? DB::table('legacy_archive_records')->where('source_table', 'users')->get(['source_id', 'payload'])
            : collect();
        $customerNames = $legacyUsers->mapWithKeys(function ($user) {
            try {
                $data = json_decode(Crypt::decryptString($user->payload), true);

                return [(string) $user->source_id => $data['name'] ?? 'Legacy customer'];
            } catch (\Throwable) {
                return [(string) $user->source_id => 'Legacy customer'];
            }
        });
        $legacyOrders = $canViewOrderDetails
            ? DB::table('legacy_archive_records')->where('source_table', 'orders')->orderByRaw('CAST(source_id AS INTEGER) DESC')->get()->map(function ($record) use ($customerNames) {
                $order = json_decode(Crypt::decryptString($record->payload), true) ?: [];
                $order['customer_name'] = $customerNames[(string) ($order['user_id'] ?? '')] ?? 'Legacy customer';

                return (object) $order;
            })
            : collect();

        $search = trim((string) request('q', ''));
        $paidOrders = StoreOrder::query()->whereIn('payment_status', ['paid', 'escrow_held'])->whereNotIn('order_status', ['cancelled', 'refunded']);
        $revenue = (float) (clone $paidOrders)->sum('total');
        $paidOrdersCount = (clone $paidOrders)->count();
        $currentMonthRevenue = (float) (clone $paidOrders)->whereBetween('created_at', [CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now()->endOfMonth()])->sum('total');
        $previousMonthRevenue = (float) (clone $paidOrders)->whereBetween('created_at', [CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth(), CarbonImmutable::now()->startOfMonth()->subSecond()])->sum('total');
        $revenueGrowth = $previousMonthRevenue > 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : null;
        $dailyRevenue = (clone $paidOrders)->whereBetween('created_at', [CarbonImmutable::today()->subDays(13)->startOfDay(), CarbonImmutable::now()])
            ->selectRaw('DATE(created_at) as sales_date, SUM(total) as revenue')->groupByRaw('DATE(created_at)')->orderBy('sales_date')->pluck('revenue', 'sales_date');
        $revenueSeries = collect(range(13, 0))->map(function (int $daysAgo) use ($dailyRevenue): array {
            $date = CarbonImmutable::today()->subDays($daysAgo);

            return ['label' => $date->format('D'), 'date' => $date->format('M j'), 'value' => (float) ($dailyRevenue[$date->toDateString()] ?? 0)];
        });
        $lowStockCount = CatalogProduct::query()->whereNotNull('stock_quantity')->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $outOfStockCount = CatalogProduct::query()->where(function ($query): void {
            $query->where('stock_quantity', 0)->orWhereRaw('LOWER(stock) = ?', ['out of stock']);
        })->count();
        $pendingInstallationsCount = DB::table('installation_bookings')->where('status', 'pending')->count();
        $installationBookingsCount = DB::table('installation_bookings')->count();

        $topSellingProducts = [];
        $salesByCategory = [];
        (clone $paidOrders)->select(['id', 'items', 'discount'])->orderBy('id')->chunkById(400, function ($orders) use (&$topSellingProducts, &$salesByCategory): void {
            foreach ($orders as $order) {
                $items = is_array($order->items) ? $order->items : (json_decode((string) $order->items, true) ?: []);
                $itemsSubtotal = collect($items)->filter(fn ($item) => is_array($item))->sum(fn ($item) => (float) ($item['price'] ?? 0) * max(0, (int) ($item['quantity'] ?? 0)));
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $name = (string) ($item['name'] ?? 'Unlabelled product');
                    $quantity = max(0, (int) ($item['quantity'] ?? 0));
                    $lineGross = (float) ($item['price'] ?? 0) * $quantity;
                    $lineDiscount = $itemsSubtotal > 0 ? min((float) $order->discount * ($lineGross / $itemsSubtotal), $lineGross) : 0;
                    $lineRevenue = max(0, $lineGross - $lineDiscount);
                    $topSellingProducts[$name]['quantity'] = ($topSellingProducts[$name]['quantity'] ?? 0) + $quantity;
                    $topSellingProducts[$name]['revenue'] = ($topSellingProducts[$name]['revenue'] ?? 0) + $lineRevenue;
                    $topSellingProducts[$name]['image'] = $item['image'] ?? null;

                    $category = trim((string) ($item['category'] ?? ''));
                    if ($category !== '') {
                        $salesByCategory[$category] = ($salesByCategory[$category] ?? 0) + $lineRevenue;
                    }
                }
            }
        });
        $topSellingProducts = collect($topSellingProducts)->sortByDesc('quantity')->take(4)->all();
        arsort($salesByCategory);

        $ordersQuery = StoreOrder::query()->when(! $canViewOrderDetails, fn ($query) => $query->whereRaw('1 = 0'))->latest('created_at')->when($search !== '', fn ($query) => $query->where(function ($orders) use ($search): void {
            $orders->where('reference', 'like', '%'.$search.'%')->orWhere('customer_name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
        }));
        $productsQuery = CatalogProduct::query()->orderByDesc('updated_at')->when($search !== '', fn ($query) => $query->where(function ($products) use ($search): void {
            $products->where('name', 'like', '%'.$search.'%')->orWhere('brand', 'like', '%'.$search.'%')->orWhere('category', 'like', '%'.$search.'%');
        }));
        $recentOrders = (clone $ordersQuery)->reorder()->latest('created_at')->limit(4)->get();

        return view('admin.index', [
            'productsCount' => CatalogProduct::count(),
            'ordersCount' => StoreOrder::count(),
            'pendingCount' => StoreOrder::where('order_status', 'pending')->count(),
            'completedCount' => StoreOrder::where('order_status', 'completed')->count(),
            'customersCount' => User::query()->where('email', '!=', config('store.admin_email'))->count(),
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'pendingInstallationsCount' => $pendingInstallationsCount,
            'installationBookingsCount' => $installationBookingsCount,
            'revenue' => $revenue,
            'averageOrderValue' => $paidOrdersCount > 0 ? $revenue / $paidOrdersCount : 0,
            'revenueGrowth' => $revenueGrowth,
            'revenueSeries' => $revenueSeries,
            'topSellingProducts' => $topSellingProducts,
            'salesByCategory' => $salesByCategory,
            'search' => $search,
            'orders' => $ordersQuery->paginate(10, ['*'], 'orders_page')->withQueryString(),
            'recentOrders' => $recentOrders,
            'products' => $productsQuery->paginate(15, ['*'], 'products_page')->withQueryString(),
            'categories' => DB::table('catalog_categories')->leftJoin('catalog_products', 'catalog_products.category', '=', 'catalog_categories.name')
                ->select('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order', DB::raw('COUNT(catalog_products.id) as product_count'))
                ->groupBy('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order')
                ->orderByRaw('CASE WHEN catalog_categories.sort_order = 0 THEN 1 ELSE 0 END')->orderBy('catalog_categories.sort_order')->orderBy('catalog_categories.name')->get(),
            'zeroPriceProducts' => CatalogProduct::where('price', 0)->orderBy('category')->orderBy('name')->paginate(50, ['*'], 'zero_price_page'),
            'zeroPriceProductsCount' => CatalogProduct::where('price', 0)->count(),
            'legacyCustomersCount' => $legacyUsers->count(), 'legacyOrders' => $legacyOrders,
        ]);
    }

    public function bulkUpdateZeroPriceStock(Request $request)
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:500'],
            'product_ids.*' => ['required', 'integer', 'distinct', Rule::exists('catalog_products', 'id')->where(fn ($query) => $query->where('price', 0))],
            'stock' => ['required', 'in:In Stock,Out of Stock,Pre-order'],
        ]);

        $updated = DB::transaction(fn () => CatalogProduct::where('price', 0)
            ->whereIn('id', $data['product_ids'])
            ->update(['stock' => $data['stock']]));

        $this->recordActivity($request, 'inventory.bulk_status_updated', null, 'Changed stock status for '.$updated.' zero-priced products to '.$data['stock'].'.');

        return redirect()->route('admin.home')->with('success', number_format($updated).' zero-priced product'.($updated === 1 ? '' : 's').' updated to '.$data['stock'].'.');
    }

    public function exportZeroPriceProducts()
    {
        $products = CatalogProduct::query()->where('price', 0)->orderBy('id')->get([
            'id', 'slug', 'name', 'sku', 'brand', 'category', 'price',
        ]);

        return response()->streamDownload(function () use ($products): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['product_id', 'slug', 'product_name', 'sku', 'brand', 'category', 'current_price_kes', 'new_price_kes']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->id,
                    $this->spreadsheetSafeText((string) $product->slug),
                    $this->spreadsheetSafeText((string) $product->name),
                    $this->spreadsheetSafeText((string) $product->sku),
                    $this->spreadsheetSafeText((string) $product->brand),
                    $this->spreadsheetSafeText((string) $product->category),
                    number_format((float) $product->price, 2, '.', ''),
                    '',
                ]);
            }

            fclose($output);
        }, 'zero-priced-products-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function importZeroPricePrices(Request $request)
    {
        $data = $request->validate([
            'pricing_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);
        $handle = fopen($data['pricing_file']->getRealPath(), 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['pricing_file' => 'The uploaded CSV could not be read.']);
        }

        $header = fgetcsv($handle);
        if (! is_array($header)) {
            fclose($handle);
            throw ValidationException::withMessages(['pricing_file' => 'The CSV is empty. Download a fresh pricing template and fill in the new_price_kes column.']);
        }
        $header = array_map(fn ($value): string => strtolower(trim((string) $value, "\xEF\xBB\xBF \t\n\r\0\x0B")), $header);
        $idColumn = array_search('product_id', $header, true);
        $priceColumn = array_search('new_price_kes', $header, true);
        if ($idColumn === false || $priceColumn === false) {
            fclose($handle);
            throw ValidationException::withMessages(['pricing_file' => 'This CSV must include the product_id and new_price_kes columns from the downloaded template.']);
        }

        $priceUpdates = [];
        $rowNumber = 1;
        $rowErrors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count(array_filter($row, fn ($cell): bool => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $id = trim((string) ($row[$idColumn] ?? ''));
            $priceValue = trim((string) ($row[$priceColumn] ?? ''));
            if ($priceValue === '') {
                continue;
            }
            if (! ctype_digit($id) || ! is_numeric($priceValue) || (float) $priceValue <= 0 || (float) $priceValue > 100000000) {
                $rowErrors[] = 'Row '.$rowNumber.': enter a valid product_id and a price greater than KES 0.';

                continue;
            }
            if (array_key_exists($id, $priceUpdates)) {
                $rowErrors[] = 'Row '.$rowNumber.': product_id '.$id.' appears more than once.';

                continue;
            }
            $priceUpdates[$id] = round((float) $priceValue, 2);
        }
        fclose($handle);

        if ($rowErrors !== []) {
            throw ValidationException::withMessages(['pricing_file' => $rowErrors]);
        }
        if ($priceUpdates === []) {
            throw ValidationException::withMessages(['pricing_file' => 'No prices were filled in. Enter a value in new_price_kes for at least one product.']);
        }

        $products = CatalogProduct::query()->whereIn('id', array_keys($priceUpdates))->get(['id', 'name', 'price'])->keyBy('id');
        $staleProducts = collect(array_keys($priceUpdates))->filter(fn ($id): bool => ! isset($products[$id]) || (float) $products[$id]->price !== 0.0);
        if ($staleProducts->isNotEmpty()) {
            throw ValidationException::withMessages(['pricing_file' => 'No changes were made. These products no longer have a KES 0 price or no longer exist: '.implode(', ', $staleProducts->all()).'. Export a fresh file and try again.']);
        }

        DB::transaction(function () use ($priceUpdates): void {
            foreach ($priceUpdates as $id => $price) {
                $updated = CatalogProduct::query()->whereKey($id)->where('price', 0)->update(['price' => $price, 'updated_at' => now()]);
                if ($updated !== 1) {
                    throw ValidationException::withMessages(['pricing_file' => 'A product price changed during the import. No changes were applied; export a fresh file and try again.']);
                }
            }
        });

        $this->recordActivity($request, 'products.zero_price_imported', null, 'Updated prices for '.count($priceUpdates).' products from the zero-price CSV upload.');

        return redirect()->route('admin.home')->with('success', number_format(count($priceUpdates)).' product price'.(count($priceUpdates) === 1 ? '' : 's').' updated from the CSV.');
    }

    private function spreadsheetSafeText(string $value): string
    {
        return preg_match('/^[\s]*[=+@\-]/u', $value) === 1 ? "'".$value : $value;
    }

    public function updateCategoryOrder(Request $request)
    {
        $categoryCount = DB::table('catalog_categories')->count();
        $data = $request->validate([
            'categories' => ['required', 'array', 'size:'.$categoryCount],
            'categories.*.id' => ['required', 'integer', 'distinct', 'exists:catalog_categories,id'],
            'categories.*.position' => ['required', 'integer', 'min:1', 'max:'.$categoryCount, 'distinct'],
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
        $this->recordActivity($request, 'categories.order_updated', null, 'Reordered catalogue categories.');

        return redirect()->route('admin.categories')->with('success', 'Category order saved across the storefront.');
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'], 'slug' => ['nullable', 'string', 'max:190'],
            'category' => ['nullable', 'string', 'max:190'], 'brand' => ['nullable', 'string', 'max:190'],
            'price' => ['required', 'numeric', 'min:0'], 'stock' => ['required', 'string', 'max:100'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('catalog_products', 'sku')],
            'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:320'],
            'description' => ['nullable', 'string'], 'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $slug = $data['slug'] ?: Str::slug($data['name']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = Str::uuid().'.'.$file->extension();
            if (config('filesystems.disks.s3.bucket')) {
                $root = trim(config('filesystems.disks.s3.root', ''), '/');
                Storage::disk('s3')->getClient()->putObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => trim($root.'/uploads/product/'.$name, '/'),
                    'Body' => file_get_contents($file->getRealPath()),
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('uploads/product'), $name);
            }
            $data['image'] = 'product/'.$name;
        }
        if (isset($data['stock_quantity'])) {
            $data['stock'] = (int) $data['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock';
        }
        unset($data['slug']);
        $product = CatalogProduct::create($data + ['slug' => $slug]);
        $this->recordActivity($request, 'product.created', (string) $product->id, 'Added product '.$product->name.' to the catalogue.');

        return redirect()->route('admin.home')->with('success', 'Product added to the catalogue.');
    }

    public function updateOrder(Request $request, int $id)
    {
        $data = $request->validate([
            'order_status' => ['required', 'in:pending,processing,shipped,completed,cancelled'],
            'payment_status' => ['required', 'in:pending_payment,paid,escrow_held,refunded'],
        ]);
        $order = StoreOrder::findOrFail($id);
        $order->update($data);
        $this->recordActivity($request, 'order.updated', (string) $order->id, 'Updated order '.$order->reference.' to '.$data['order_status'].' / '.$data['payment_status'].'.');

        return redirect()->route('admin.orders')->with('success', 'Order status updated.');
    }

    private function recordActivity(Request $request, string $action, ?string $subjectId, string $summary): void
    {
        DB::table('admin_activity_logs')->insert([
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => $subjectId === null ? null : str($action)->before('.')->toString(),
            'subject_id' => $subjectId,
            'summary' => $summary,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

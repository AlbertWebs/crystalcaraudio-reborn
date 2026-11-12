<?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AdminBusinessIntelligenceController;
use App\Http\Controllers\AdminCatalogExportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminInstallationBookingController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\AdminMediaController;
use App\Http\Controllers\AdminPortfolioController;
use App\Http\Controllers\AdminProductsController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminSeoController;
use App\Http\Controllers\AdminStoreManagementController;
use App\Http\Controllers\AdminTwoFactorController;
use App\Http\Controllers\AdminUsersController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StorefrontInstallationRequestController;
use App\Support\PortfolioCatalog;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/public', fn () => redirect('/', 301));
Route::get('/public/{path}', fn (string $path) => redirect('/'.ltrim($path, '/'), 301))->where('path', '.*');
Route::get('/home', [StorefrontController::class, 'home'])->name('home-page');
Route::get('/premium-car-audio-systems-nairobi-kenya', [StorefrontController::class, 'premiumCarAudio'])->name('premium-car-audio');
Route::get('/products/car-audio-systems', [StorefrontController::class, 'carAudioSystems'])->name('car-audio-systems');
Route::get('/car-audio-systems', fn () => redirect()->route('car-audio-systems', [], 301));
Route::get('/products', [StorefrontController::class, 'products'])->name('categories');
Route::get('/products/category', [StorefrontController::class, 'products'])->name('category');
Route::get('/shop-by-category', [StorefrontController::class, 'categoryDirectory'])->name('shop-by-category');
Route::get('/products/shop-by-category', fn () => redirect()->route('shop-by-category', [], 301));
Route::get('/products/shop-by-brand', [StorefrontController::class, 'brandDirectory'])->name('shop-by-brand');
Route::get('/shop-by-brand', fn () => redirect()->route('shop-by-brand', [], 301));
Route::get('/products/brand', fn () => redirect()->route('shop-by-brand', [], 301));
Route::get('/products/brand/{brand}', [StorefrontController::class, 'products'])->name('products.brand');
Route::get('/products/car-audio-installation', fn () => redirect()->route('portfolio', [], 301));
Route::get('/car-speakers', [StorefrontController::class, 'products'])->defaults('slug', 'car-speakers')->name('car-speakers');
Route::get('/products/mid-range-speakers', fn () => redirect()->route('car-speakers', [], 301));
Route::get('/products/car-speakers', fn () => redirect()->route('car-speakers', [], 301));
Route::get('/products/car-audio-system', fn () => redirect()->route('car-audio-systems', [], 301));
Route::get('/products/car-audo-system', fn () => redirect()->route('car-audio-systems', [], 301));
Route::get('/products/car-security-system', fn () => redirect()->route('car-audio-systems', [], 301));
Route::get('/products/{slug}', [StorefrontController::class, 'products'])->name('product-category');
Route::get('/special-offers', [StorefrontController::class, 'products'])->name('special-offers');
Route::get('/product/{slug}', [StorefrontController::class, 'product'])->name('product-single');
Route::get('/product-quick-view/{slug}', [StorefrontController::class, 'product'])->name('product-quick-view');
Route::get('/product-variation/{slug}', [StorefrontController::class, 'product'])->name('product-single-variation');
Route::get('/popup/{slug}', fn (string $slug) => redirect()->route('product-single', $slug, 301))->name('popup');
Route::get('/search', [StorefrontController::class, 'products'])->name('search');
Route::get('/search-results', [StorefrontController::class, 'products'])->name('search-results');
Route::get('/filter', [StorefrontController::class, 'products'])->name('search-filter');
Route::get('/filters', [StorefrontController::class, 'products'])->name('search-filters');
Route::get('/product-tags/{slug}', [StorefrontController::class, 'products'])->name('product-tags');
Route::get('/sitemap.xml', function () {
    $categoryPriorities = [
        'car-radio' => '0.90',
        'car-speakers' => '0.80',
        'car-amplifiers-booster' => '0.70',
        'equalizers' => '0.60',
    ];
    $priorityCategorySlugs = array_keys($categoryPriorities);
    $categoryUrls = DB::table('catalog_categories')
        ->whereNotIn('slug', ['car-audio-installation', 'car-audio-system', 'car-audio-systems'])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['slug'])
        ->sortBy(function (object $category) use ($priorityCategorySlugs): int {
            $position = array_search($category->slug, $priorityCategorySlugs, true);

            return $position === false ? count($priorityCategorySlugs) : $position;
        })
        ->map(fn (object $category): array => [
            'loc' => $category->slug === 'car-speakers' ? url('/car-speakers') : url('/products/'.$category->slug),
            'changefreq' => 'weekly',
            'priority' => $categoryPriorities[$category->slug] ?? '0.50',
        ]);

    $staticUrls = [
        '/products' => '0.50',
        '/shop-by-category' => '0.50',
        '/shop-by-brand' => '0.40',
        '/premium-car-audio-systems-nairobi-kenya' => '0.50',
        '/products/car-audio-systems' => '0.80',
        '/special-offers' => '0.50',
        '/about-us' => '0.40',
        '/find-us' => '0.40',
        '/our-portfolio' => '0.40',
        '/shipping-policy' => '0.30',
        '/terms-and-conditions' => '0.30',
        '/privacy-policy' => '0.30',
    ];

    $urls = collect([[
        'loc' => url('/'),
        'changefreq' => 'daily',
        'priority' => '1.00',
    ]])->concat($categoryUrls);

    foreach ($staticUrls as $path => $priority) {
        $urls->push([
            'loc' => url($path),
            'changefreq' => 'weekly',
            'priority' => $priority,
        ]);
    }

    $urls = $urls
        ->concat(DB::table('catalog_products')->whereNotNull('slug')->pluck('slug')->map(fn (string $slug): array => [
            'loc' => url('/product/'.$slug),
            'changefreq' => 'weekly',
            'priority' => '0.40',
        ]))
        ->concat(DB::table('catalog_tags')->pluck('slug')->map(fn (string $slug): array => [
            'loc' => url('/product-tags/'.$slug),
            'changefreq' => 'weekly',
            'priority' => '0.30',
        ]))
        ->concat(DB::table('catalog_products')->whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand')->map(fn (string $brand): array => [
            'loc' => url('/products/brand/'.Str::slug($brand)),
            'changefreq' => 'weekly',
            'priority' => '0.30',
        ]))
        ->concat(app(PortfolioCatalog::class)->all()->map(fn (array $project): array => [
            'loc' => url('/our-portfolio/'.$project['slug']),
            'changefreq' => 'monthly',
            'priority' => '0.30',
        ]))
        ->unique('loc')
        ->values();

    return response()->view('store.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');
Route::get('/sitemap', fn () => redirect()->route('sitemap', [], 301));

Route::get('/shopping-cart', [StorefrontController::class, 'cart'])->name('cart');
Route::post('/shopping-cart/add/{product}', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::get('/shopping-cart/add-to-cart/{id}', [StorefrontController::class, 'legacyAddToCart'])->name('add.to.cart');
Route::post('/shopping-cart/update', [StorefrontController::class, 'updateCart'])->name('cart.update');
Route::post('/shopping-cart/update-cart', [StorefrontController::class, 'updateCart'])->name('update.cart');
Route::get('/shopping-cart/remove-from-cart/{id}', [StorefrontController::class, 'removeFromCart'])->name('remove.from.cart');
Route::get('/shopping-cart/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/shopping-cart/checkout/login', [CustomerController::class, 'login'])->middleware('throttle:5,1')->name('checkout.login');
Route::post('/shopping-cart/checkout/create-user', [CustomerController::class, 'register'])->middleware('throttle:5,1')->name('checkout.create');
Route::get('/checkout', fn () => redirect()->route('checkout', [], 301));
Route::get('/checkout/payment', fn () => redirect()->route('checkout', [], 301));
Route::get('/checkout/payment-last', fn () => redirect()->route('checkout', [], 301));
Route::get('/checkout/placeOrder', fn () => redirect()->route('checkout', [], 301));
Route::post('/checkout/placeOrder', [CheckoutController::class, 'placeOrder']);
Route::post('/checkout/process-coupon', [CheckoutController::class, 'processCoupon']);
Route::get('/checkout/remove-coupon/{code}', [CheckoutController::class, 'removeCoupon']);
Route::post('/shopping-cart/checkout/process-coupon', [CheckoutController::class, 'processCoupon'])->name('process_coupon');
Route::get('/shopping-cart/checkout/remove-coupon/{code}', [CheckoutController::class, 'removeCoupon'])->name('remove-coupon');
Route::get('/shopping-cart/checkout/payment', [CheckoutController::class, 'index'])->name('payment');
Route::get('/shopping-cart/checkout/payment-last', [CheckoutController::class, 'index'])->name('payments');
Route::get('/shopping-cart/checkout/placeOrder', [CheckoutController::class, 'index'])->name('checkout.order.get');
Route::post('/shopping-cart/checkout/placeOrder', [CheckoutController::class, 'placeOrder']);
Route::post('/shopping-cart/checkout/placeOrder', [CheckoutController::class, 'placeOrder'])->name('checkout.order');
Route::get('/checkout/econfirm/{reference}', [CheckoutController::class, 'pay'])->name('checkout.pay');
Route::post('/checkout/econfirm/{reference}/reconcile', [CheckoutController::class, 'reconcile'])->middleware('throttle:30,1')->name('checkout.reconcile');
Route::get('/checkout/complete/{reference}', [CheckoutController::class, 'complete'])->name('checkout.complete');
Route::post('/api/webhooks/econfirm/checkout', [CheckoutController::class, 'webhook'])->name('webhooks.econfirm')->withoutMiddleware([ValidateCsrfToken::class]);
Route::get('/do-not', fn () => redirect()->route('home', [], 302))->name('do-not');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::get('/login', [CustomerController::class, 'loginForm'])->name('login');
Route::post('/login', [CustomerController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::get('/forgot-password', [CustomerController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [CustomerController::class, 'sendPasswordResetLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [CustomerController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [CustomerController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
Route::get('/register', [CustomerController::class, 'registerForm'])->name('register');
Route::post('/register', [CustomerController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard.home');
    Route::get('/wishlist', [CustomerController::class, 'dashboard'])->name('wishlist');
    Route::post('/dashboard/update-settings', [CustomerController::class, 'update'])->name('dashboard.update');
    Route::post('/customer/logout', [CustomerController::class, 'logout'])->name('customer.logout');
    Route::get('/dashboard/logout', [CustomerController::class, 'logout'])->name('customer.logout.legacy');
    Route::post('/wishlist/add/{product}', [CustomerController::class, 'addWishlist'])->name('wishlist.add');
    Route::get('/wishlist/add-to-wishlist/{id}', [CustomerController::class, 'legacyAddWishlist'])->name('add.to.wishlist');
    Route::delete('/wishlist/remove/{id}', [CustomerController::class, 'removeWishlist'])->name('wishlist.remove');
    Route::get('/wishlist/remove-from-wishlist/{id}', [CustomerController::class, 'removeWishlist'])->name('remove.from.wishlist');
});
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::get('/admin/verify-email-code', [AdminTwoFactorController::class, 'show'])->name('admin.two-factor');
Route::post('/admin/verify-email-code', [AdminTwoFactorController::class, 'verify'])->middleware('throttle:6,1')->name('admin.two-factor.verify');
Route::post('/admin/verify-email-code/resend', [AdminTwoFactorController::class, 'resend'])->middleware('throttle:3,1')->name('admin.two-factor.resend');
Route::middleware(['auth', 'can:manage-store'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::get('/bi', [AdminBusinessIntelligenceController::class, 'index'])->middleware('can:view-admin-bi')->name('bi');
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->middleware('can:manage-store-inventory')->name('inventory');
    Route::patch('/inventory/{product}', [AdminInventoryController::class, 'update'])->middleware('can:manage-store-inventory')->name('inventory.update');
    Route::get('/categories', [AdminStoreManagementController::class, 'categories'])->middleware('can:manage-store-products')->name('categories');
    Route::post('/categories', [AdminStoreManagementController::class, 'storeCategory'])->middleware('can:manage-store-products')->name('categories.store');
    Route::patch('/categories/{category}', [AdminStoreManagementController::class, 'updateCategory'])->middleware('can:manage-store-products')->name('categories.update');
    Route::patch('/categories/{category}/image', [AdminStoreManagementController::class, 'updateCategoryImage'])->middleware('can:manage-store-products')->name('categories.image');
    Route::get('/brands', [AdminStoreManagementController::class, 'brands'])->middleware('can:manage-store-products')->name('brands');
    Route::get('/exports', [AdminCatalogExportController::class, 'index'])->middleware('can:manage-store-products')->name('exports');
    Route::get('/exports/{format}', [AdminCatalogExportController::class, 'download'])->whereIn('format', ['merchant', 'csv', 'excel'])->middleware('can:manage-store-products')->name('exports.download');
    Route::get('/media', [AdminMediaController::class, 'index'])->middleware('can:manage-store-products')->name('media');
    Route::put('/media/{assetKey}', [AdminMediaController::class, 'update'])->where('assetKey', '[a-z0-9_]+')->middleware('can:manage-store-products')->name('media.update');
    Route::get('/seo', [AdminSeoController::class, 'edit'])->middleware('can:manage-store-products')->name('seo');
    Route::put('/seo', [AdminSeoController::class, 'update'])->middleware('can:manage-store-products')->name('seo.update');
    Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->middleware('can:manage-store-products')->name('portfolio');
    Route::get('/portfolio/product-search', [AdminPortfolioController::class, 'searchProducts'])->middleware('can:manage-store-products')->name('portfolio.product-search');
    Route::get('/portfolio/create', [AdminPortfolioController::class, 'create'])->middleware('can:manage-store-products')->name('portfolio.create');
    Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->middleware('can:manage-store-products')->name('portfolio.store');
    Route::get('/portfolio/{project}/edit', [AdminPortfolioController::class, 'edit'])->middleware('can:manage-store-products')->name('portfolio.edit');
    Route::patch('/portfolio/{project}', [AdminPortfolioController::class, 'update'])->middleware('can:manage-store-products')->name('portfolio.update');
    Route::get('/orders', [AdminStoreManagementController::class, 'orders'])->middleware('can:manage-store-orders')->name('orders');
    Route::get('/customers', [AdminStoreManagementController::class, 'customers'])->middleware('can:manage-store-orders')->name('customers');
    Route::get('/installations', [AdminInstallationBookingController::class, 'index'])->middleware('can:manage-installations')->name('installations');
    Route::post('/installations', [AdminInstallationBookingController::class, 'store'])->middleware('can:manage-installations')->name('installations.store');
    Route::patch('/installations/{booking}', [AdminInstallationBookingController::class, 'update'])->middleware('can:manage-installations')->name('installations.update');
    Route::get('/users', [AdminUsersController::class, 'index'])->middleware('can:manage-store-users')->name('users');
    Route::patch('/users/{user}/role', [AdminUsersController::class, 'updateRole'])->middleware('can:manage-store-users')->name('users.role');
    Route::get('/activity', [AdminActivityController::class, 'index'])->middleware('can:manage-store-users')->name('activity');
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile/security', [AdminProfileController::class, 'updateSecurity'])->name('profile.security');
    Route::put('/categories/order', [AdminController::class, 'updateCategoryOrder'])->middleware('can:manage-store-products')->name('categories.order');
    Route::get('/products', [AdminProductsController::class, 'index'])->middleware('can:manage-store-products')->name('products');
    Route::get('/products/create', [AdminProductsController::class, 'create'])->middleware('can:manage-store-products')->name('products.create');
    Route::get('/products/{product}/edit', [AdminProductsController::class, 'edit'])->middleware('can:manage-store-products')->name('products.edit');
    Route::post('/products', [AdminProductsController::class, 'store'])->middleware('can:manage-store-products')->name('products.store');
    Route::patch('/products/zero-price-stock', [AdminController::class, 'bulkUpdateZeroPriceStock'])->middleware('can:manage-store-inventory')->name('products.zero-price-stock');
    Route::get('/products/zero-price-prices.csv', [AdminController::class, 'exportZeroPriceProducts'])->middleware('can:manage-store-products')->name('products.zero-price-prices.export');
    Route::post('/products/zero-price-prices.csv', [AdminController::class, 'importZeroPricePrices'])->middleware('can:manage-store-products')->name('products.zero-price-prices.import');
    Route::patch('/products/{product}', [AdminProductsController::class, 'update'])->middleware('can:manage-store-products')->name('products.update');
    Route::delete('/products/{product}', [AdminProductsController::class, 'destroy'])->middleware('can:manage-store-products')->name('products.destroy');
    Route::patch('/orders/{id}', [AdminController::class, 'updateOrder'])->middleware('can:manage-store-orders')->name('orders.update');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});

// Keep legacy informational and campaign paths resolvable while the CMS content is migrated.
foreach (['find-us', 'about-us', 'terms-and-conditions', 'privacy-policy', 'shipping-policy', 'delivery', 'copyright'] as $slug) {
    Route::get('/'.$slug, [StorefrontController::class, 'info'])->defaults('slug', $slug)->name('page.'.$slug);
}
Route::get('/our-portfolio', [StorefrontController::class, 'portfolio'])->name('portfolio');
Route::post('/our-portfolio/installation-requests', [StorefrontInstallationRequestController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('portfolio.installation-requests.store');
Route::get('/our-portfolio/{slug}', [StorefrontController::class, 'portfolioProject'])->name('folio');

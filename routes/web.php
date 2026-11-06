<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/home', [StorefrontController::class, 'home'])->name('home-page');
Route::get('/products', [StorefrontController::class, 'products'])->name('categories');
Route::get('/products/category', [StorefrontController::class, 'products'])->name('category');
Route::get('/products/shop-by-category', [StorefrontController::class, 'products'])->name('shop-by-category');
Route::get('/products/shop-by-brand', [StorefrontController::class, 'products'])->name('shop-by-brand');
Route::get('/products/brand/{brand}', [StorefrontController::class, 'products'])->name('products.brand');
Route::get('/products/{slug}', [StorefrontController::class, 'products'])->name('product-category');
Route::get('/special-offers', [StorefrontController::class, 'products'])->name('special-offers');
Route::get('/product/{slug}', [StorefrontController::class, 'product'])->name('product-single');
Route::get('/product-quick-view/{slug}', [StorefrontController::class, 'product'])->name('product-quick-view');
Route::get('/product-variation/{slug}', [StorefrontController::class, 'product'])->name('product-single-variation');
Route::get('/search', [StorefrontController::class, 'products'])->name('search');
Route::get('/search-results', [StorefrontController::class, 'products'])->name('search-results');
Route::get('/filter', [StorefrontController::class, 'products'])->name('search-filter');
Route::get('/filters', [StorefrontController::class, 'products'])->name('search-filters');
Route::get('/product-tags/{slug}', [StorefrontController::class, 'products'])->name('product-tags');
Route::get('/sitemap', function () {
    $paths = collect();
    $paths = $paths->merge(\Illuminate\Support\Facades\DB::table('catalog_products')->pluck('slug')->map(fn ($slug) => '/product/'.$slug));
    $paths = $paths->merge(\Illuminate\Support\Facades\DB::table('catalog_categories')->pluck('slug')->map(fn ($slug) => '/products/'.$slug));
    $paths = $paths->merge(\Illuminate\Support\Facades\DB::table('catalog_tags')->pluck('slug')->map(fn ($slug) => '/product-tags/'.$slug));
    $paths = $paths->merge(['/products','/special-offers','/about-us','/find-us','/shipping-policy','/terms-and-conditions','/privacy-policy']);
    return response()->view('store.sitemap', ['paths' => $paths->unique()->values()])->header('Content-Type','application/xml; charset=UTF-8');
})->name('sitemap');
Route::get('/sitemap.xml', fn () => redirect()->route('sitemap', [], 301));

Route::get('/shopping-cart', [StorefrontController::class, 'cart'])->name('cart');
Route::post('/shopping-cart/add/{product}', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::get('/shopping-cart/add-to-cart/{id}', [StorefrontController::class, 'legacyAddToCart'])->name('add.to.cart');
Route::post('/shopping-cart/update', [StorefrontController::class, 'updateCart'])->name('cart.update');
Route::post('/shopping-cart/update-cart', [StorefrontController::class, 'updateCart'])->name('update.cart');
Route::get('/shopping-cart/remove-from-cart/{id}', [StorefrontController::class, 'removeFromCart'])->name('remove.from.cart');
Route::get('/shopping-cart/checkout', [CheckoutController::class, 'index'])->name('checkout');
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
Route::post('/api/webhooks/econfirm/checkout', [CheckoutController::class, 'webhook'])->name('webhooks.econfirm')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

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
    Route::post('/wishlist/add/{product}', [CustomerController::class, 'addWishlist'])->name('wishlist.add');
    Route::get('/wishlist/add-to-wishlist/{id}', [CustomerController::class, 'legacyAddWishlist'])->name('add.to.wishlist');
    Route::delete('/wishlist/remove/{id}', [CustomerController::class, 'removeWishlist'])->name('wishlist.remove');
    Route::get('/wishlist/remove-from-wishlist/{id}', [CustomerController::class, 'removeWishlist'])->name('remove.from.wishlist');
});
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.post');
Route::middleware(['auth', 'can:manage-store'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::put('/categories/order', [AdminController::class, 'updateCategoryOrder'])->name('categories.order');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::patch('/orders/{id}', [AdminController::class, 'updateOrder'])->name('orders.update');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});

// Keep legacy informational and campaign paths resolvable while the CMS content is migrated.
foreach (['find-us','about-us','terms-and-conditions','privacy-policy','shipping-policy','delivery','copyright','our-portfolio'] as $slug) {
    Route::get('/'.$slug, [StorefrontController::class, 'info'])->defaults('slug', $slug)->name('page.'.$slug);
}
Route::get('/our-portfolio/{id}', [StorefrontController::class, 'info'])->defaults('slug', 'our-portfolio')->name('folio');

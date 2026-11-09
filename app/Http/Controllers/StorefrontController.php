<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Support\MediaUrl;
use App\Support\PageSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function home()
    {
        $bestSellers = CatalogProduct::query()
            ->where(fn ($query) => $query->where('featured', true)->orWhere('trending', true))
            ->orderByDesc('featured')->orderByDesc('trending')->latest()->take(8)->get();
        if ($bestSellers->count() < 4) {
            $bestSellers = CatalogProduct::query()->orderByDesc('featured')->latest()->take(8)->get();
        }
        $deals = CatalogProduct::query()->where(fn ($query) => $query->where('is_offer', true)->orWhere('is_special', true))
            ->orderByDesc('featured')->latest()->take(8)->get();
        $newArrivals = CatalogProduct::query()->latest()->take(8)->get();
        $comboProducts = CatalogProduct::query()
            ->where('is_combo', true)
            ->orderByDesc('featured')->orderByDesc('updated_at')->take(2)->get();
        if ($comboProducts->count() < 2) {
            $fallbackProducts = CatalogProduct::query()
                ->whereIn('slug', ['car-door-speakers-65-320w', 'android-screen-underseat-subwoofer'])
                ->get();
            $comboProducts = $comboProducts
                ->concat($fallbackProducts->whereNotIn('id', $comboProducts->pluck('id')))
                ->take(2)
                ->values();
        }
        if ($comboProducts->count() < 2) {
            $additionalProducts = CatalogProduct::query()
                ->whereNotIn('id', $comboProducts->pluck('id'))
                ->orderByDesc('featured')->latest()->take(2 - $comboProducts->count())->get();
            $comboProducts = $comboProducts->concat($additionalProducts)->values();
        }
        $comboSlides = $comboProducts->isEmpty()
            ? collect()
            : collect([$comboProducts->take(2)->values()]);
        $categories = DB::table('catalog_categories')
            ->leftJoin('catalog_products', 'catalog_products.category', '=', 'catalog_categories.name')
            ->select('catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order', DB::raw('COUNT(catalog_products.id) as product_count'))
            ->groupBy('catalog_categories.id', 'catalog_categories.name', 'catalog_categories.slug', 'catalog_categories.image', 'catalog_categories.sort_order')
            ->orderByRaw('CASE WHEN catalog_categories.sort_order = 0 THEN 1 ELSE 0 END')
            ->orderBy('catalog_categories.sort_order')->orderBy('catalog_categories.name')->get();
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->limit(12)->pluck('brand');

        return view('store.home', compact('bestSellers', 'deals', 'newArrivals', 'comboSlides', 'categories', 'brands'));
    }

    public function premiumCarAudio()
    {
        $sections = collect([
            [
                'eyebrow' => '01 / HEAD UNITS',
                'title' => 'Start with the right car radio',
                'description' => 'Explore Android screens, Bluetooth head units and vehicle-specific radios. Check each listing for features and fitment details, then ask our team if you need help choosing for your car.',
                'categories' => ['Car Radio'],
                'link' => route('product-category', 'car-radio'),
                'link_label' => 'Browse car radios',
            ],
            [
                'eyebrow' => '02 / SPEAKERS & BASS',
                'title' => 'Bring more clarity and depth to every drive',
                'description' => 'Pair quality door speakers with a suitable amplifier or subwoofer for a fuller in-car sound. Browse speaker and audio-system options, and confirm sizes and compatibility before ordering.',
                'categories' => ['Car Speakers', 'Car Audio System'],
                'link' => route('product-category', 'mid-range-speakers'),
                'link_label' => 'Browse car speakers',
            ],
            [
                'eyebrow' => '03 / AMPLIFIERS & EQUALIZERS',
                'title' => 'Fine-tune the system around your setup',
                'description' => 'Find amplifiers and equalizers to shape the power and balance of your system. If you are building a complete setup, contact Crystal Car Audio for product and installation guidance.',
                'categories' => ['Car Booster Amplifiers', 'Equalizers'],
                'link' => route('product-category', 'car-amplifiers-booster'),
                'link_label' => 'Browse amplifiers',
            ],
        ])->map(function (array $section) {
            $section['products'] = CatalogProduct::query()
                ->whereIn('category', $section['categories'])
                ->whereNotNull('image')->where('image', '!=', '')
                ->orderByDesc('featured')->orderByDesc('trending')->latest()->take(2)->get();

            return $section;
        });
        $heroProduct = $sections->first()['products']->first() ?? null;
        $heroImageUrl = $heroProduct?->image
            ? MediaUrl::url('uploads/'.$heroProduct->image)
            : MediaUrl::url('uploads/logo/Crystal%20Logo-06.png');

        return view('store.premium-car-audio', compact('sections', 'heroProduct', 'heroImageUrl'));
    }

    public function products(Request $request, ?string $slug = null, ?string $brand = null)
    {
        $brand ??= $request->route('brand') ? (string) $request->route('brand') : null;
        $query = CatalogProduct::query();
        $brandName = $brand ? CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand')->first(fn ($name) => Str::slug($name) === $brand) : null;
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%"));
        }
        $category = $request->query('category');
        $categoryRecord = null;
        if ($request->route()?->getName() === 'product-category') {
            $categoryRecord = DB::table('catalog_categories')->where('slug', $slug)->first();
            $category = $categoryRecord->name ?? $category ?? $slug;
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($brand) {
            $query->where('brand', $brandName ?? $brand);
        }
        if ($selectedBrand = trim((string) $request->query('brand'))) {
            $query->where('brand', $selectedBrand);
        }
        if ($request->route()?->getName() === 'product-tags' && $slug) {
            $tagId = DB::table('catalog_tags')->where('slug', $slug)->value('legacy_id');
            $query->where('legacy_tag_id', $tagId ?? '__missing_tag__');
        }
        if ($request->route()?->getName() === 'special-offers') {
            $query->where(fn ($q) => $q->where('is_offer', true)->orWhere('is_special', true));
        }
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        if (is_numeric($minPrice)) {
            $query->where('price', '>=', max(0, (float) $minPrice));
        }
        if (is_numeric($maxPrice)) {
            $query->where('price', '<=', max(0, (float) $maxPrice));
        }
        $sort = (string) $request->query('sort', 'recommended');
        match ($sort) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderBy('price')->orderByDesc('featured'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('featured'),
            default => $query->orderByDesc('featured')->latest(),
        };
        $products = $query->paginate(24)->withQueryString();
        $categories = DB::table('catalog_categories')->orderByRaw('CASE WHEN sort_order = 0 THEN 1 ELSE 0 END')->orderBy('sort_order')->orderBy('name')->get();
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');
        $pageTitle = 'Find your sound.';
        $pageDescription = 'Thoughtfully selected audio and accessories for the way you drive.';
        $seoTitle = 'Car Audio, Speakers & Accessories in Kenya | Crystal Car Audio';
        $seoDescription = 'Shop car audio, speakers, multimedia and accessories at Crystal Car Audio Kenya. Get expert advice, secure checkout and delivery across Kenya.';
        $seoKeywords = 'car audio Kenya, car radios Nairobi, car speakers, car accessories, Crystal Car Audio';
        $seoOgTitle = $seoTitle;
        $seoOgDescription = $seoDescription;
        $categoryGuide = null;
        if ($category) {
            $categoryRecord ??= DB::table('catalog_categories')->where('name', $category)->first();
            $pageTitle = $categoryRecord->name ?? $category;
            $categoryDescription = filled($categoryRecord->description ?? null)
                ? trim(preg_replace('/\\s+/', ' ', html_entity_decode(strip_tags((string) $categoryRecord->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
                : '';
            $categoryDescription = preg_replace('/\\s*—\\s*/u', ', ', $categoryDescription);
            $categoryDescription = preg_replace('/(?<=[.!?])(?=[A-Z])/u', ' ', $categoryDescription);
            if (mb_strlen($categoryDescription) > 260) {
                $categoryGuide = $categoryDescription;
                $pageDescription = 'Browse '.strtolower($pageTitle).' selected for clean, reliable upgrades. Get expert fitment advice and arrange delivery across Kenya.';
            } else {
                $pageDescription = $categoryDescription ?: 'Shop '.strtolower($pageTitle).' at Crystal Car Audio Kenya. Find the right fit for your vehicle with expert advice and delivery across Kenya.';
            }
            $seoTitle = $pageTitle.' in Kenya | Crystal Car Audio';
            $seoDescription = Str::limit($pageDescription, 155);
        } elseif ($request->route()?->getName() === 'special-offers') {
            $pageTitle = 'Special offers.';
            $pageDescription = 'A little more sound for a little less. Browse current Crystal Car Audio deals.';
            $seoTitle = 'Car Audio Deals & Special Offers in Kenya | Crystal Car Audio';
            $seoDescription = $pageDescription;
        } elseif ($request->route()?->getName() === 'product-tags' && $slug) {
            $pageTitle = (string) (DB::table('catalog_tags')->where('slug', $slug)->value('name') ?? 'Tagged products');
            $pageDescription = 'Explore the '.$pageTitle.' collection.';
            $seoTitle = $pageTitle.' Products in Kenya | Crystal Car Audio';
            $seoDescription = $pageDescription.' Shop selected products with expert advice and delivery across Kenya.';
        }

        if ($request->route()?->getName() === 'products.brand' && $brand) {
            $brandName ??= str_replace('-', ' ', $brand);
            $pageTitle = $brandName;
            $pageDescription = 'Browse '.$brandName.' products available from Crystal Car Audio in Kenya.';
            $seo = PageSeo::resolve('brand_detail', [
                'title' => '{brand} Car Audio & Accessories in Nairobi | Crystal Car Audio',
                'description' => 'Shop {brand} car audio and vehicle accessories in Nairobi, Kenya. Browse available products and get advice from Crystal Car Audio.',
                'keywords' => '{brand} car audio, {brand} accessories Nairobi, {brand} Kenya',
                'og_title' => '', 'og_description' => '',
            ]);
            $seoTitle = str_replace('{brand}', $brandName, $seo['title']);
            $seoDescription = Str::limit(str_replace('{brand}', $brandName, $seo['description']), 160);
            $seoKeywords = str_replace('{brand}', $brandName, $seo['keywords']);
            $seoOgTitle = filled($seo['og_title']) ? str_replace('{brand}', $brandName, $seo['og_title']) : $seoTitle;
            $seoOgDescription = filled($seo['og_description']) ? Str::limit(str_replace('{brand}', $brandName, $seo['og_description']), 160) : $seoDescription;
        } elseif ($category) {
            $seo = PageSeo::resolve('category_detail', [
                'title' => '{category} in Kenya | Crystal Car Audio',
                'description' => 'Shop {category} at Crystal Car Audio Kenya. Find the right fit for your vehicle with expert advice and delivery across Kenya.',
                'keywords' => '{category}, car audio Kenya, car accessories Nairobi',
                'og_title' => '', 'og_description' => '',
            ]);
            $seoTitle = str_replace('{category}', $pageTitle, $seo['title']);
            $seoDescription = Str::limit(str_replace('{category}', $pageTitle, $seo['description']), 160);
            $seoKeywords = str_replace('{category}', $pageTitle, $seo['keywords']);
            $seoOgTitle = filled($seo['og_title']) ? str_replace('{category}', $pageTitle, $seo['og_title']) : $seoTitle;
            $seoOgDescription = filled($seo['og_description']) ? Str::limit(str_replace('{category}', $pageTitle, $seo['og_description']), 160) : $seoDescription;
        }

        return view('store.products', compact('products', 'pageTitle', 'pageDescription', 'categoryGuide', 'seoTitle', 'seoDescription', 'seoKeywords', 'seoOgTitle', 'seoOgDescription', 'categories', 'brands'));
    }

    public function categoryDirectory()
    {
        $productCounts = DB::table('catalog_products')->select('category', DB::raw('COUNT(*) as product_count'))->groupBy('category');
        $categories = DB::table('catalog_categories')
            ->leftJoinSub($productCounts, 'product_counts', 'product_counts.category', '=', 'catalog_categories.name')
            ->select('catalog_categories.*', DB::raw('COALESCE(product_counts.product_count, 0) as product_count'))
            ->orderByRaw('CASE WHEN catalog_categories.sort_order = 0 THEN 1 ELSE 0 END')
            ->orderBy('catalog_categories.sort_order')->orderBy('catalog_categories.name')->get();
        $seo = PageSeo::resolve('shop_by_category', [
            'title' => 'Shop Car Audio Categories in Kenya | Crystal Car Audio',
            'description' => 'Browse car radios, speakers, amplifiers, security systems, accessories and installation services at Crystal Car Audio Kenya. Get expert advice and delivery across Kenya.',
            'keywords' => 'car audio categories Kenya, car radios, car speakers, car amplifiers, car alarm systems, car audio installation Nairobi',
            'og_title' => '', 'og_description' => '',
        ]);

        return view('store.category-directory', compact('categories', 'seo'));
    }

    public function brandDirectory()
    {
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')
            ->select('brand', DB::raw('COUNT(*) as product_count'))->groupBy('brand')->orderBy('brand')->get();
        $seo = PageSeo::resolve('shop_by_brand', [
            'title' => 'Shop Car Audio Brands in Kenya | Crystal Car Audio',
            'description' => 'Explore JVC, Pioneer, Sony, Kenwood, Boschmann and more car audio brands at Crystal Car Audio in Nairobi, Kenya.',
            'keywords' => 'car audio brands Kenya, JVC car audio, Pioneer car speakers, Sony car audio, Kenwood car speakers, Boschmann',
            'og_title' => '', 'og_description' => '',
        ]);

        return view('store.brand-directory', compact('brands', 'seo'));
    }

    public function portfolio()
    {
        $projects = $this->portfolioRecords();
        $seo = PageSeo::resolve('portfolio', [
            'title' => 'Car Audio Installation Portfolio in Nairobi | Crystal Car Audio',
            'description' => 'Explore Crystal Car Audio installations across Nairobi and Kenya, from Android radio upgrades to complete speaker and subwoofer systems.',
            'keywords' => 'car audio installation Nairobi, car radio upgrade Kenya, car speaker installation, car subwoofer installation',
            'og_title' => '', 'og_description' => '',
        ]);

        return view('store.portfolio', compact('projects', 'seo'));
    }

    public function portfolioProject(string $id)
    {
        $project = $this->portfolioRecords()->first(fn ($record) => (string) ($record['id'] ?? '') === (string) $id);
        abort_unless($project, 404);
        $seo = PageSeo::resolve('portfolio', [
            'title' => 'Car Audio Installation Portfolio in Nairobi | Crystal Car Audio',
            'description' => 'Explore Crystal Car Audio installations across Nairobi and Kenya, from Android radio upgrades to complete speaker and subwoofer systems.',
            'keywords' => 'car audio installation Nairobi, car radio upgrade Kenya, car speaker installation, car subwoofer installation',
            'og_title' => '', 'og_description' => '',
        ]);
        $seo['title'] = trim(strip_tags((string) ($project['title'] ?? 'Car Audio Installation'))).' | Crystal Car Audio';
        $seo['description'] = Str::limit(trim(strip_tags((string) ($project['content'] ?? 'Car audio installation by Crystal Car Audio in Nairobi, Kenya.'))), 160);

        return view('store.portfolio-project', compact('project', 'seo'));
    }

    private function portfolioRecords()
    {
        return DB::table('legacy_archive_records')->where('source_table', 'portfolio')->orderBy('source_id')->get(['source_id', 'payload'])
            ->map(function ($row) {
                try {
                    $record = json_decode(Crypt::decryptString($row->payload), true) ?: [];
                    $record['id'] ??= $row->source_id;
                    $record['content'] = preg_replace('/\\s*—\\s*/u', ', ', preg_replace('/rnrn/i', "\n\n", (string) ($record['content'] ?? '')));
                    $record['images'] = collect($record)->filter(fn ($value, $key) => preg_match('/^image_/', (string) $key) && filled($value))
                        ->map(fn ($filename) => MediaUrl::url('uploads/portfolio/'.ltrim((string) $filename, '/')))->values()->all();

                    return $record;
                } catch (\Throwable) {
                    return null;
                }
            })->filter(fn ($record) => is_array($record) && filled($record['title'] ?? null))->values();
    }

    public function product(string $slug)
    {
        $product = CatalogProduct::where('slug', $slug)->firstOrFail();
        $categoryRecord = $product->category
            ? DB::table('catalog_categories')->where('name', $product->category)->first(['name', 'slug'])
            : null;
        $relatedProducts = CatalogProduct::query()->where('category', $product->category)->whereKeyNot($product->id)->latest()->take(4)->get();
        $description = trim((string) $product->description);
        $description = preg_replace('/rnrn/i', "\n", $description);
        $description = preg_replace('/\\s*—\\s*/u', ', ', $description);
        $descriptionPlain = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $descriptionPlain = trim(preg_replace('/\s+/', ' ', $descriptionPlain));
        $description = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6])\b[^>]*>/i', "\n", $description);
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = preg_replace('/[\t ]+/', ' ', $description);
        $detailParagraphs = collect(preg_split('/\R+/', $description) ?: [])
            ->map(fn ($line) => trim($line, " \t\n\r\0\x0B•-"))
            ->filter(fn ($line) => mb_strlen($line) > 2 && $line !== '0')
            ->unique()->values();
        $shortSpecifications = collect($product->specifications)->filter(function ($spec) {
            return is_array($spec) && filled($spec['title'] ?? null) && filled($spec['value'] ?? null)
                && mb_strlen(strip_tags((string) $spec['title'])) <= 48
                && mb_strlen(strip_tags((string) $spec['value'])) <= 90;
        })->values();

        $reviews = DB::table('legacy_archive_records')->where('source_table', 'reviews')->get(['payload'])
            ->map(function ($row) {
                try {
                    return json_decode(Crypt::decryptString($row->payload), true) ?: null;
                } catch (\Throwable) {
                    return null;
                }
            })->filter(function ($review) use ($product) {
                return is_array($review)
                    && (string) ($review['product_id'] ?? '') === (string) $product->legacy_id
                    && in_array(strtolower((string) ($review['status'] ?? '')), ['1', 'approved', 'active'], true);
            })->map(fn ($review) => [
                'title' => trim(strip_tags((string) ($review['title'] ?? ''))),
                'content' => trim(preg_replace('/\\s*—\\s*/u', ', ', strip_tags((string) ($review['content'] ?? $review['review'] ?? '')))),
                'name' => trim(strip_tags((string) ($review['name'] ?? 'Crystal customer'))),
                'rating' => min(5, max(0, (float) ($review['rating'] ?? 0) / 20)),
                'created_at' => $review['created_at'] ?? null,
            ])->filter(fn ($review) => $review['rating'] > 0 && ($review['title'] !== '' || $review['content'] !== ''))->values();

        return view('store.product', compact('product', 'categoryRecord', 'relatedProducts', 'detailParagraphs', 'descriptionPlain', 'shortSpecifications', 'reviews'));
    }

    public function info(string $slug, ?string $id = null)
    {
        $source = match ($slug) {
            'about-us' => 'about', 'terms-and-conditions' => 'terms', 'privacy-policy' => 'privacy',
            'shipping-policy', 'delivery' => 'delivery', 'copyright' => 'copyright',
            'our-portfolio' => 'portfolio', 'find-us' => 'sitesettings', default => null,
        };
        $records = collect();
        if ($source) {
            $records = DB::table('legacy_archive_records')->where('source_table', $source)->orderBy('source_id')->get(['source_id', 'payload'])
                ->map(function ($row) {
                    try {
                        return json_decode(Crypt::decryptString($row->payload), true) ?: [];
                    } catch (\Throwable) {
                        return [];
                    }
                })->filter()->values();
            if ($id !== null) {
                $records = $records->where('id', $id)->values();
            }
        }

        return view('store.info', ['slug' => $slug, 'records' => $records]);
    }

    public function cart()
    {
        $cart = collect(session('cart', []));
        $cartProductIds = $cart->keys()->filter(fn ($id) => is_numeric($id))->values();
        $cartCategories = $cartProductIds->isNotEmpty()
            ? CatalogProduct::query()->whereIn('id', $cartProductIds)->pluck('category')->filter()->unique()->values()
            : collect();
        $recommendations = CatalogProduct::query()->whereNotIn('id', $cartProductIds)->whereNotNull('image')->where('image', '!=', '')
            ->when($cartCategories->isNotEmpty(), fn ($query) => $query->whereIn('category', $cartCategories))
            ->orderByDesc('featured')->orderByDesc('trending')->latest()->take(4)->get();
        if ($recommendations->count() < 4) {
            $recommendations = $recommendations->merge(
                CatalogProduct::query()->whereNotIn('id', $cartProductIds->merge($recommendations->modelKeys()))->whereNotNull('image')->where('image', '!=', '')
                    ->orderByDesc('featured')->orderByDesc('trending')->latest()->take(4 - $recommendations->count())->get()
            )->unique('id')->take(4)->values();
        }

        return view('store.cart', ['cart' => $cart, 'total' => $cart->sum(fn ($item) => $item['price'] * $item['quantity']), 'recommendations' => $recommendations]);
    }

    public function addToCart(Request $request, CatalogProduct $product)
    {
        $cart = session('cart', []);
        $item = $cart[$product->id] ?? ['name' => $product->name, 'price' => (float) $product->price, 'image' => $product->image, 'quantity' => 0];
        $item['product_id'] = $product->id;
        $item['category'] = $product->category;
        $item['brand'] = $product->brand;
        $item['quantity'] += max(1, min(10, (int) $request->input('quantity', 1)));
        $cart[$product->id] = $item;
        session(['cart' => $cart]);
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout');
        }
        if ($request->expectsJson()) {
            $cartItems = collect($cart);
            $cartCount = (int) $cartItems->sum('quantity');
            $cartSubtotal = (float) $cartItems->sum(fn ($cartItem) => (float) ($cartItem['price'] ?? 0) * (int) ($cartItem['quantity'] ?? 0));

            return response()->json([
                'message' => 'Added to your bag.',
                'product_name' => $product->name,
                'product_image' => $product->image ? MediaUrl::url(str_starts_with($product->image, 'http') ? $product->image : 'uploads/'.$product->image) : null,
                'cart_count' => $cartCount,
                'mini_cart_html' => view('store.partials.mini-cart-content', compact('cartItems', 'cartCount', 'cartSubtotal'))->render(),
            ]);
        }

        return redirect()->route('cart')->with('success', 'Added to your cart.');
    }

    public function updateCart(Request $request)
    {
        $quantities = $request->validate(['quantity' => ['required', 'array'], 'quantity.*' => ['integer', 'min:0', 'max:10']])['quantity'];
        $cart = session('cart', []);
        foreach ($quantities as $id => $quantity) {
            if (! isset($cart[$id])) {
                continue;
            }
            if ((int) $quantity === 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = (int) $quantity;
            }
        }
        session(['cart' => $cart]);

        return redirect()->route('cart');
    }

    public function legacyAddToCart(Request $request, string $id)
    {
        $product = CatalogProduct::findOrFail($id);

        return $this->addToCart($request, $product);
    }

    public function removeFromCart(string $id)
    {
        $cart = session('cart', []);
        unset($cart[$id]);
        session(['cart' => $cart]);

        return redirect()->route('cart');
    }
}

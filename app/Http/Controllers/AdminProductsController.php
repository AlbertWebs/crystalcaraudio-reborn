<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProductsController extends Controller
{
    public function create(): View
    {
        return view('admin.product-form', $this->formOptions());
    }

    public function edit(CatalogProduct $product): View
    {
        return view('admin.product-form', $this->formOptions($product));
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $category = $request->string('category')->toString();
        $brand = $request->string('brand')->toString();
        $stock = $request->string('stock')->toString();
        $priceState = $request->string('price')->toString();
        $sort = $request->string('sort')->toString();

        $products = CatalogProduct::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%');
            }))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($brand !== '', fn ($query) => $query->where('brand', $brand))
            ->when($stock !== '', fn ($query) => $query->where('stock', $stock))
            ->when($priceState === 'zero', fn ($query) => $query->where('price', 0))
            ->when($priceState === 'priced', fn ($query) => $query->where('price', '>', 0))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'price_low', fn ($query) => $query->orderBy('price')->orderBy('name'))
            ->when($sort === 'price_high', fn ($query) => $query->orderByDesc('price')->orderBy('name'))
            ->when($sort === 'stock_low', fn ($query) => $query->orderByRaw('CASE WHEN stock_quantity IS NULL THEN 0 ELSE 1 END')->orderBy('stock_quantity'))
            ->when(! in_array($sort, ['name', 'price_low', 'price_high', 'stock_low'], true), fn ($query) => $query->latest('updated_at'))
            ->paginate(20)
            ->withQueryString();

        $categories = DB::table('catalog_categories')->orderBy('sort_order')->orderBy('name')->pluck('name')
            ->merge(CatalogProduct::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'))
            ->unique()->values();
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');

        return view('admin.products', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => compact('search', 'category', 'brand', 'stock', 'priceState', 'sort'),
            'totalProducts' => CatalogProduct::query()->count(),
            'zeroPriceCount' => CatalogProduct::query()->where('price', 0)->count(),
            'outOfStockCount' => CatalogProduct::query()->where(fn ($query) => $query->where('stock_quantity', 0)->orWhereRaw('LOWER(stock) = ?', ['out of stock']))->count(),
            'featuredCount' => CatalogProduct::query()->where('featured', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validatedProduct($request);
        $data = $this->normalizeProductData($request, $data);
        $product = DB::transaction(function () use ($data, $request): CatalogProduct {
            $product = CatalogProduct::query()->create($data);
            $this->recordInitialStock($request, $product);
            $this->recordActivity($request, 'product.created', $product, 'Added '.$product->name.' to the catalogue.');

            return $product;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => $product->name.' was added to the catalogue.', 'redirect' => route('admin.products')], 201);
        }

        return redirect()->route('admin.products')->with('success', $product->name.' was added to the catalogue.');
    }

    public function update(Request $request, CatalogProduct $product): RedirectResponse|JsonResponse
    {
        $data = $this->validatedProduct($request, $product);
        $data = $this->normalizeProductData($request, $data, $product);

        DB::transaction(function () use ($data, $request, $product): void {
            $before = $product->stock_quantity === null ? null : (int) $product->stock_quantity;
            $product->update($data);
            $after = $product->stock_quantity === null ? null : (int) $product->stock_quantity;

            if ($after !== $before && $after !== null) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $product->id,
                    'created_by' => $request->user()->id,
                    'quantity_change' => $before === null ? $after : $after - $before,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Product details updated',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->recordActivity($request, 'product.updated', $product, 'Updated catalogue details for '.$product->name.'.');
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => $product->name.' was updated.', 'redirect' => route('admin.products')]);
        }

        return redirect()->route('admin.products')->with('success', $product->name.' was updated.');
    }

    public function destroy(Request $request, CatalogProduct $product): RedirectResponse
    {
        $name = $product->name;
        $this->recordActivity($request, 'product.deleted', $product, 'Removed '.$name.' from the catalogue.');
        $product->delete();

        return redirect()->route('admin.products')->with('success', $name.' was removed from the catalogue.');
    }

    /** @return array<string, mixed> */
    private function validatedProduct(Request $request, ?CatalogProduct $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('catalog_products', 'slug')->ignore($product?->id)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('catalog_products', 'sku')->ignore($product?->id)],
            'category' => ['nullable', 'string', 'max:190'],
            'brand' => ['nullable', 'string', 'max:190'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'stock' => ['required', 'in:In Stock,Out of Stock,Pre-order'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'max:5120'],
            'featured' => ['nullable', 'boolean'],
            'is_offer' => ['nullable', 'boolean'],
            'is_special' => ['nullable', 'boolean'],
            'trending' => ['nullable', 'boolean'],
        ]);
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeProductData(Request $request, array $data, ?CatalogProduct $product = null): array
    {
        $slug = trim((string) ($data['slug'] ?? '')) !== '' ? Str::slug($data['slug']) : Str::slug($data['name']);
        $baseSlug = $slug;
        $suffix = 2;
        while (CatalogProduct::query()->where('slug', $slug)->when($product, fn ($query) => $query->where('id', '!=', $product->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }
        $data['slug'] = $slug;
        $data['low_stock_threshold'] = (int) ($data['low_stock_threshold'] ?? 5);
        foreach (['featured', 'is_offer', 'is_special', 'trending'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        if (array_key_exists('description', $data)) {
            $data['description'] = $this->sanitizeDescription((string) $data['description']);
        }

        if (array_key_exists('stock_quantity', $data) && $data['stock_quantity'] !== null) {
            $data['stock'] = (int) $data['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock';
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        } elseif ($product) {
            unset($data['image']);
        }

        return $data;
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->extension();
        $path = 'product/'.$name;

        if (config('filesystems.disks.s3.bucket')) {
            $root = trim((string) config('filesystems.disks.s3.root', ''), '/');
            Storage::disk('s3')->getClient()->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => trim($root.'/uploads/'.$path, '/'),
                'Body' => file_get_contents($file->getRealPath()),
                'ContentType' => $file->getMimeType(),
            ]);
        } else {
            $directory = public_path('uploads/product');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $file->move($directory, $name);
        }

        return $path;
    }

    private function recordInitialStock(Request $request, CatalogProduct $product): void
    {
        if ($product->stock_quantity === null) {
            return;
        }

        DB::table('inventory_movements')->insert([
            'product_id' => $product->id,
            'created_by' => $request->user()->id,
            'quantity_change' => (int) $product->stock_quantity,
            'quantity_before' => null,
            'quantity_after' => (int) $product->stock_quantity,
            'reason' => 'Initial product stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function recordActivity(Request $request, string $action, CatalogProduct $product, string $summary): void
    {
        DB::table('admin_activity_logs')->insert([
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => 'catalog_product',
            'subject_id' => (string) $product->id,
            'summary' => $summary,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array{categories: Collection, brands: Collection, editingProduct: ?CatalogProduct, descriptionHtml: string} */
    private function formOptions(?CatalogProduct $editingProduct = null): array
    {
        $categories = DB::table('catalog_categories')->orderBy('sort_order')->orderBy('name')->pluck('name')
            ->merge(CatalogProduct::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'))
            ->unique()->values();
        $brands = CatalogProduct::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');

        $descriptionHtml = $this->sanitizeDescription((string) old('description', $editingProduct?->description ?? ''));

        return compact('categories', 'brands', 'editingProduct', 'descriptionHtml');
    }

    private function sanitizeDescription(string $description): string
    {
        $allowedTags = '<p><br><strong><b><em><i><ul><ol><li><h2><h3>';
        $description = strip_tags($description, $allowedTags);

        return preg_replace_callback('/<(\/?)\s*(p|br|strong|b|em|i|ul|ol|li|h2|h3)\b[^>]*>/i', fn (array $tag): string => '<'.$tag[1].strtolower($tag[2]).'>', $description) ?? '';
    }
}

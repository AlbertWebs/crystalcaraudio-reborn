<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSeoController extends Controller
{
    private const PAGES = [
        'shop_by_category' => [
            'label' => 'Shop by category directory', 'path' => '/shop-by-category',
            'title' => 'Shop Car Audio Categories in Kenya | Crystal Car Audio',
            'description' => 'Browse car radios, speakers, amplifiers, security systems, accessories and installation services at Crystal Car Audio Kenya. Get expert advice and delivery across Kenya.',
            'keywords' => 'car audio categories Kenya, car radios, car speakers, car amplifiers, car alarm systems, car audio installation Nairobi',
        ],
        'shop_by_brand' => [
            'label' => 'Shop by brand directory', 'path' => '/products/shop-by-brand',
            'title' => 'Shop Car Audio Brands in Kenya | Crystal Car Audio',
            'description' => 'Explore JVC, Pioneer, Sony, Kenwood, Boschmann and more car audio brands at Crystal Car Audio in Nairobi, Kenya.',
            'keywords' => 'car audio brands Kenya, JVC car audio, Pioneer car speakers, Sony car audio, Kenwood car speakers, Boschmann',
        ],
        'category_detail' => [
            'label' => 'Category page template', 'path' => '/products/{category}',
            'title' => '{category} in Kenya | Crystal Car Audio',
            'description' => 'Shop {category} at Crystal Car Audio Kenya. Find the right fit for your vehicle with expert advice and delivery across Kenya.',
            'keywords' => '{category}, car audio Kenya, car accessories Nairobi',
        ],
        'brand_detail' => [
            'label' => 'Brand page template', 'path' => '/products/brand/{brand}',
            'title' => '{brand} Car Audio & Accessories in Nairobi | Crystal Car Audio',
            'description' => 'Shop {brand} car audio and vehicle accessories in Nairobi, Kenya. Browse available products and get advice from Crystal Car Audio.',
            'keywords' => '{brand} car audio, {brand} accessories Nairobi, {brand} Kenya',
        ],
        'portfolio' => [
            'label' => 'Installation portfolio', 'path' => '/our-portfolio',
            'title' => 'Car Audio Installation Portfolio in Nairobi | Crystal Car Audio',
            'description' => 'Explore Crystal Car Audio installations across Nairobi and Kenya, from Android radio upgrades to complete speaker and subwoofer systems.',
            'keywords' => 'car audio installation Nairobi, car radio upgrade Kenya, car speaker installation, car subwoofer installation',
        ],
    ];

    public function edit(): View
    {
        $settings = DB::table('page_seo_settings')->get()->keyBy('page_key');
        $pages = collect(self::PAGES)->map(function (array $page, string $key) use ($settings): array {
            $record = $settings->get($key);
            foreach (['title', 'description', 'keywords', 'og_title', 'og_description'] as $field) {
                $page[$field] = $record?->{$field} ?: ($field === 'og_title' ? $page['title'] : ($field === 'og_description' ? $page['description'] : $page[$field]));
            }
            $page['key'] = $key;

            return $page;
        });

        return view('admin.seo', compact('pages'));
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = ['pages' => ['required', 'array']];
        foreach (self::PAGES as $key => $_page) {
            $rules['pages.'.$key.'.title'] = ['required', 'string', 'max:255'];
            $rules['pages.'.$key.'.description'] = ['required', 'string', 'max:320'];
            $rules['pages.'.$key.'.keywords'] = ['nullable', 'string', 'max:500'];
            $rules['pages.'.$key.'.og_title'] = ['nullable', 'string', 'max:255'];
            $rules['pages.'.$key.'.og_description'] = ['nullable', 'string', 'max:320'];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $request): void {
            foreach (self::PAGES as $key => $_page) {
                $values = $data['pages'][$key];
                DB::table('page_seo_settings')->updateOrInsert(
                    ['page_key' => $key],
                    [...$values, 'updated_at' => now(), 'created_at' => now()],
                );
            }

            DB::table('admin_activity_logs')->insert([
                'user_id' => $request->user()->id,
                'action' => 'seo.pages_updated',
                'subject_type' => 'page_seo_settings',
                'summary' => 'Updated SEO metadata for storefront landing pages and listing templates.',
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('admin.seo')->with('success', 'Page SEO settings saved.');
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

class PageSeo
{
    /** @param array<string, string> $defaults
     * @return array<string, string>
     */
    public static function resolve(string $pageKey, array $defaults): array
    {
        try {
            $settings = DB::table('page_seo_settings')->where('page_key', $pageKey)->first();
        } catch (Throwable) {
            return $defaults;
        }

        if (! $settings) {
            return $defaults;
        }

        foreach (['title', 'description', 'keywords', 'og_title', 'og_description'] as $field) {
            if (filled($settings->{$field} ?? null)) {
                $defaults[$field] = (string) $settings->{$field};
            }
        }

        return $defaults;
    }
}

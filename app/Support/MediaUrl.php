<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;

class MediaUrl
{
    public static function siteAsset(string $key, string $fallback): string
    {
        try {
            $path = DB::table('site_media_assets')->where('asset_key', $key)->value('path');
            if (is_string($path) && $path !== '') {
                return str_starts_with($path, 'images/') ? asset($path) : self::url($path);
            }
        } catch (Throwable $exception) {
            // Keep storefronts rendering during deployment before migrations run.
            report($exception);
        }

        return str_starts_with($fallback, 'images/') ? asset($fallback) : self::url($fallback);
    }

    public static function category(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (str_starts_with($normalizedPath, 'images/')) {
            return asset($normalizedPath);
        }

        return self::url('uploads/'.$normalizedPath);
    }

    public static function url(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $localPath = ltrim($path, '/');
        $s3Path = ltrim(rawurldecode($path), '/');

        if (config('filesystems.disks.s3.bucket') && config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
            try {
                return Storage::disk('s3')->temporaryUrl($s3Path, now()->addDays(7));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return asset($localPath);
    }
}

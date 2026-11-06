<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaUrl
{
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

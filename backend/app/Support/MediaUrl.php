<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaUrl
{
    public static function for(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $diskName = config('catalog.media_disk', 'public');
        $disk = Storage::disk($diskName);

        if (in_array($diskName, ['r2', 's3'], true)) {
            try {
                return $disk->temporaryUrl(
                    $path,
                    now()->addSeconds((int) config('catalog.media_url_ttl', 21600)),
                );
            } catch (Throwable) {
                // Fall through to the public URL.
            }
        }

        return $disk->url($path);
    }
}

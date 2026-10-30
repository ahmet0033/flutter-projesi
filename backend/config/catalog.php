<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog media disk
    |--------------------------------------------------------------------------
    |
    | Local development uses the public disk. Set CATALOG_MEDIA_DISK=r2 in
    | production to serve product images from Cloudflare R2.
    |
    */

    'media_disk' => env('CATALOG_MEDIA_DISK', 'public'),

    'media_url_ttl' => (int) env('CATALOG_MEDIA_URL_TTL', 21600),
];

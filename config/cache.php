<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'file' => [
            'driver' => 'file',
            // Always use the absolute path; AppServiceProvider auto-creates it
            'path' => storage_path('framework/cache/data'),
        ],
    ],

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache'),
];

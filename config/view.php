<?php

/*
|--------------------------------------------------------------------------
| View Storage Paths
|--------------------------------------------------------------------------
| IMPORTANT: We do NOT use realpath() here because it returns FALSE when
| the directory does not exist (e.g. right after a fresh ZIP extract),
| which then causes "View path not found." errors. Instead we return the
| plain path; the AppServiceProvider guarantees the directory exists.
*/

$compiledPath = storage_path('framework/views');

if (! is_dir($compiledPath)) {
    @mkdir($compiledPath, 0775, true);
}

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => $compiledPath,
];

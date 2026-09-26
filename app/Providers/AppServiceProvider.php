<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
        |------------------------------------------------------------------
        | Force the file cache path EARLY (before any cache access)
        |------------------------------------------------------------------
        | This prevents the "Please provide a valid cache path" error even
        | if the storage/framework/cache/data folder was deleted or lost
        | during a ZIP extraction / deployment.
        */
        $this->app->booted(function () {
            $cachePath = storage_path('framework/cache/data');
            if (! is_dir($cachePath)) {
                @mkdir($cachePath, 0775, true);
            }
            // Re-bind the file store with the guaranteed path
            if ($this->app['config']->get('cache.stores.file')) {
                $this->app['config']->set('cache.stores.file.path', $cachePath);
            }
        });
    }

    public function boot()
    {
        Schema::defaultStringLength(191);

        /*
        |------------------------------------------------------------------
        | Auto-create ALL required storage / cache / framework directories
        |------------------------------------------------------------------
        | Runs on every request; mkdir is a no-op when the folder already
        | exists, so this is safe and cheap. It guarantees a fresh ZIP
        | extract will never hit a "cache path" or "view path" error.
        */
        $dirs = [
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/testing'),
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (is_dir($dir) && ! is_writable($dir)) {
                @chmod($dir, 0775);
            }
        }

        // Drop a .gitkeep so the folder is never lost in future ZIP/git exports
        foreach ([
            storage_path('framework/cache/data/.gitkeep'),
            storage_path('framework/sessions/.gitkeep'),
            storage_path('framework/views/.gitkeep'),
            storage_path('logs/.gitkeep'),
            base_path('bootstrap/cache/.gitkeep'),
        ] as $keepFile) {
            if (! file_exists($keepFile)) {
                @file_put_contents($keepFile, "");
            }
        }
    }
}

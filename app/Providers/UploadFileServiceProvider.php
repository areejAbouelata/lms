<?php

namespace App\Providers;

use App\Services\UploadFileService;
use Illuminate\Support\ServiceProvider;

class UploadFileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Service\UploadFileService', function ($app) {
            return new UploadFileService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

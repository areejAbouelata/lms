<?php

namespace App\Providers;

use App\Services\PhoneNumberService;
use Illuminate\Support\ServiceProvider;

class PhoneNumberServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
     
        $this->app->bind('App\Services\PhoneNumberService', function ($app) {
            return new PhoneNumberService();
          });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
    
}

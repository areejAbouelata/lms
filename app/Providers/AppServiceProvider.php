<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Carbon\Carbon::setLocale(LC_TIME, app()->getLocale());
        view()->composer([
            'dashboard.layout.sidebar'
        ], function ($view) {
            $view->with('locale', app()->getLocale());
        });

        view()->composer([
            'dashboard.layout.header',
            'dashboard.layout.script'
        ], function ($view) {
            $view->with('notifications', auth()->check() ? auth()->user()->unreadnotifications()->paginate(50) : null);
        });
    }
}

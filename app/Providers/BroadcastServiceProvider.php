<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (auth('api')->check()) {
            $guard = "auth:api";
        }/*elseif (auth()->check()) {
            $guard = "guest";
        }*/ else {
            $guard = "web";
        }

        Broadcast::routes(['middleware' => $guard]);

        require base_path('routes/channels.php');
    }
}

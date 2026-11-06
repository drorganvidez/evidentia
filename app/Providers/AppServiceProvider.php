<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * The https scheme behind the reverse proxies comes from the trusted proxies (bootstrap/app.php).
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $timezone = config('finance.timezone', 'UTC');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}

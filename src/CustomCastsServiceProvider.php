<?php

namespace Vkovic\LaravelCustomCasts;

use Illuminate\Support\ServiceProvider;

use function Vkovic\LaravelCustomCasts\package_path;

class CustomCastsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            package_path('config') => config_path()
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(package_path('config/custom_casts.php'), 'custom_casts');
    }
}
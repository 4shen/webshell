<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider as Provider;
use Schema;

class App extends Provider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel db fix
        Schema::defaultStringLength(191);

        // @todo Remove the if control after 1.3 update
        if (method_exists('Blade', 'withoutDoubleEncoding')) {
            Blade::withoutDoubleEncoding();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.installed') && config('app.debug')) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        if (config('app.env') !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}

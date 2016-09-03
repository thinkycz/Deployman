<?php

namespace App\Providers;

use App\Services\RemoteConsole;
use Illuminate\Support\ServiceProvider;

class RemoteConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RemoteConsole::class, function () {
            return new RemoteConsole();
        });
    }
}

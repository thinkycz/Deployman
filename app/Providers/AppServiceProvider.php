<?php

namespace App\Providers;

use App\Services\BaseDeployer;
use App\Services\RemoteConsole;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        $this->app->singleton(BaseDeployer::class, function () {
            return new BaseDeployer(app(RemoteConsole::class));
        });
    }
}

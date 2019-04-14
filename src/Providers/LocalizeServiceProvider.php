<?php


namespace Eslym\EasyLocalize\Providers;


use Eslym\EasyLocalize\Contracts\Localize as LocalizeContract;
use Eslym\EasyLocalize\Middleware\RedirectLocale;
use Eslym\EasyLocalize\Middleware\SetLocale;
use Eslym\EasyLocalize\Tools\Localize;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class LocalizeServiceProvider extends ServiceProvider
{
    public function boot(){
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('locale-redirect', RedirectLocale::class);
        $router->aliasMiddleware('locale-load', SetLocale::class);
        $router->pushMiddlewareToGroup('web', 'locale-load');
        $this->app->singleton(LocalizeContract::class, function (){
            $available = array_map('basename', glob(resource_path('lang/*/')));
            return new Localize(config('locale.available', $available));
        });
        $this->app->alias(LocalizeContract::class, 'localize');
    }

    public function register()
    {
        $this->publishes([
            __DIR__.'/../../config/localize.php' => config_path('localize.php')
        ], 'config');
    }
}
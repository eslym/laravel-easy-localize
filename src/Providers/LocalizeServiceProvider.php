<?php


namespace Eslym\EasyLocalize\Providers;


use Eslym\EasyLocalize\Contracts\Localize as LocalizeContract;
use Eslym\EasyLocalize\Middleware\RedirectLocale;
use Eslym\EasyLocalize\Middleware\SetLocale;
use Eslym\EasyLocalize\Tools\Localize;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionProperty;

class LocalizeServiceProvider extends ServiceProvider
{
    public function boot(){
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('locale-redirect', RedirectLocale::class);

        ## Hack into kernel to inject global middleware
        $kernel = $this->app->get(Kernel::class);
        try {
            $prop = new ReflectionProperty($kernel, 'middleware');
            $prop->setAccessible(true);
            if(is_array($middleware = $prop->getValue($kernel))){
                array_unshift($middleware, SetLocale::class);
                $prop->setValue($kernel, $middleware);
            }
        } catch (ReflectionException $e) {
        }

        $this->app->singleton(LocalizeContract::class, function (){
            return new Localize(
                $this->app->get(Request::class),
                $this->app->get(Router::class)
            );
        });
        $this->app->alias(LocalizeContract::class, 'localize');

        /**
         * Get the original route name before localized
         *
         * @return string
         * @instantiated
         */
        Route::macro('getOriginalName', function () : ?string{
            /** @var Route $self */
            $self = $this;
            if(isset($self->action['originalName'])){
                return $self->action['originalName'];
            } else {
                return $self->getName();
            }
        });

        /**
         * Match the current original route name with pattern
         *
         * @param string[] $patterns
         * @return boolean
         * @instantiated
         */
        Route::macro('originalNamed', function (...$patterns)
        {
            /** @var Route $self */
            $self = $this;
            if (is_null($routeName = $self->getOriginalName())) {
                return false;
            }
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return true;
                }
            }
            return false;
        });
    }

    public function register()
    {
        if(function_exists('config_path')){
            $this->publishes([
                __DIR__.'/../../config/localize.php' => config_path('localize.php')
            ], 'config');
        }
    }
}
<?php


namespace Eslym\EasyLocalize\Tools;


use Closure;
use Eslym\EasyLocalize\Contracts\Localize as LocalizeContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Localize implements LocalizeContract
{

    protected $available = [];

    protected $accepts = [];

    protected $alias = [];

    protected $pattern = '';

    protected $routeNames = [];

    protected $request;

    protected $router;

    protected $routing = false;

    protected $current;

    public function __construct(Request $request, Router $router)
    {
        $available = array_unique(Config::get('localize.available',[]));
        $available = array_unique(array_merge($available, [
            Config::get('app.locale', 'en'),
            Config::get('app.fallback_locale', 'en')
        ]));
        $settings = Config::get('localize.settings', []);
        foreach ($available as $lang){
            if(isset($settings[$lang])){
                $this->available []= $lang;
                if(isset($settings[$lang]['group'])){
                    if(!isset($this->alias[$group = $settings[$lang]['group']])){
                        $this->alias[$group] = [];
                    }
                    $this->alias[$group][]=$lang;
                }
            }
        }
        $this->available = array_unique($this->available);
        $this->accepts = array_unique(array_merge(
            $this->available,
            array_keys($this->alias)
        ));
        $list = array_map('urlencode', $this->accepts);
        $list = array_map('preg_quote', $list);
        $this->pattern = '/^\/?('.join('|', $list).')(?:\/|\/?$|\/?\?)/';
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * @param Closure $routes
     */
    public function routes(Closure $routes): void
    {
        if($this->routing){
            $this->router->middleware([])->group($routes);
            return;
        }
        $this->routing = true;
        $originalRoutes = $this->router->getRoutes();
        $this->router->middleware([])
            ->group($routes);
        foreach ($this->router->getRoutes() as $route) {
            /** @var Route $route */
            $headGet = array_intersect($route->methods, ['HEAD', 'GET']);
            $others = array_diff($route->methods, ['HEAD', 'GET']);
            if (count($others) > 0){
                $clone = clone $route;
                $clone->methods = $others;
                $originalRoutes->add($clone);
            }
            if(count($headGet) > 0){
                $route->methods = $headGet;
                $originalRoutes->add($route);
            }
        }
        $this->router->setRoutes($originalRoutes);
        foreach ($this->accepts as $lang){
            $originalRoutes = $this->router->getRoutes();
            $this->router->setRoutes(new RouteCollection());
            $this->router->middleware("locale-load:$lang")
                ->group($routes);
            foreach ($this->router->getRoutes() as $route){
                /** @var Route $route */
                $route->methods = array_intersect($route->methods, ['GET', 'HEAD']);
                if(count($route->methods) > 0){
                    $uri = $route->uri();
                    $route->setUri(rtrim($lang.Str::start($uri, '/'), '/'));
                    $name = $route->getName();
                    $route->name(empty($name) ? $name : ".$lang");
                    $route->action['originalName'] = $name;
                    $originalRoutes->add($route);
                }
            }
            $this->router->setRoutes($originalRoutes);
        }
        $this->routing = false;
    }

    /**
     * @param string|string[] $language
     * @param string $uri
     * @return string|string[]
     */
    public function to($language, $uri = null)
    {
        $uri = $uri ?? $this->request->getRequestUri();
        $original = ltrim(preg_replace($this->pattern, '', $uri), '/');
        if(is_string($language)){
            $language = urlencode($language);
            return Url::to("$language/$original");
        } else if (is_array($language)){
            return array_combine($language, array_map([$this, 'to'], $language));
        } else {
            throw new InvalidArgumentException('Invalid type, string or array expected.');
        }
    }

    /**
     * @return string[]
     */
    public function accepts(): array
    {
        return $this->accepts;
    }

    /**
     * @return string[]
     */
    public function available(): array
    {
        return $this->available;
    }

    /**
     * @return array[]
     */
    public function aliases(): array
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function current(): string
    {
        return $this->current ?? (
            $this->current = (
                $this->fromUri() ??
                $this->request->cookie('language') ??
                $this->request->getPreferredLanguage($this->accepts) ??
                Config::get('app.locale', 'en')
            ));
    }

    /**
     * @param string $language
     * @return string|array
     */
    public function name(?string $language = null)
    {
        if($language === null){
            return collect(Config::get('localize.settings'))
                ->only($this->available)
                ->map(function($lang){return data_get($lang, 'name');})
                ->toArray();
        }
        if(!in_array($language, $this->available)){
            return null;
        }
        return Config::get("localize.settings.$language.name", $language);
    }

    /**
     * @param string|null $language
     * @return array
     */
    public function setting(?string $language = null): ?array{
        if($language === null){
            return Arr::only(Config::get('localize.settings'), $this->available);
        }
        if(!in_array($language, $this->available)){
            return null;
        }
        return Config::get("localize.settings.$language", null);
    }

    public function fromUri(): ?string{
        if(preg_match($this->pattern, $this->request->getRequestUri(), $matches)){
            return $matches[1];
        }
        return null;
    }
}
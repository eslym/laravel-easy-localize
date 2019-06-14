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

    public function __construct(array $available, Request $request, Router $router)
    {
        $this->available = array_unique($available);
        foreach ($this->available as $lang){
            $sub = explode('-', $lang);
            if(count($sub) == 1 || in_array($sub[0], $this->available)){
                continue;
            }
            $this->alias = array_merge_recursive(
                $this->alias,
                [$sub[0] => [$lang]]
            );
        }
        $this->accepts = array_merge(
            $this->available,
            array_keys($this->alias)
        );
        $list = array_map('urlencode', $this->accepts);
        $list = array_map('preg_quote', $list);
        $this->pattern = '/^\/?(?:'.join('|', $list).')(?:\/|\/?$|\/?\?)/';
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
        $this->router->middleware('locale-redirect')
            ->group($routes);
        foreach ($this->accepts as $lang){
            $originalRoutes = $this->router->getRoutes();
            $this->router->setRoutes(new RouteCollection());
            $this->router->middleware("locale-load:$lang")
                ->group($routes);
            foreach ($this->router->getRoutes() as $route){
                /** @var Route $route */
                $methods = array_flip($route->methods);
                $methods = array_keys(Arr::only($methods, ['GET', 'HEAD']));
                if(count($methods) > 0){
                    $route->methods = $methods;
                    $uri = $route->uri();
                    $route->setUri($lang.Str::start($uri, '/'));
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
        return $this->request->cookie(
            'language',
            $this->request->getPreferredLanguage($this->accepts)
        ) ?? Config::get('app.locale', 'en');
    }

    /**
     * @param string $language
     * @return string
     */
    public function name(string $language): string
    {
        return Config::get("localize.names.$language", $language);
    }
}
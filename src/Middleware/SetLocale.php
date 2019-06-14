<?php


namespace Eslym\EasyLocalize\Middleware;


use Closure;
use Eslym\EasyLocalize\Facades\Localize;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;

class SetLocale
{
    protected $cookie;

    public function __construct(CookieFactory $cookie)
    {

        $this->cookie = $cookie;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $language
     * @return mixed
     */
    public function handle($request, Closure $next, $language = null)
    {
        $lang = $language ?? Localize::current();
        if(isset(Localize::aliases()[$lang])){
            if($request->isMethod('GET')){
                $aliases = Localize::aliases()[$lang];
                $to = in_array(Localize::current(), $aliases) ?
                    Localize::current() : $aliases[0];
                return Redirect::to(Localize::to($to));
            } else {
                $lang = Localize::current();
            }
        }
        App::setLocale($lang);
        /** @var Response $response */
        $response = $next($request);
        return $response->withCookie($this->cookie->forever('language', App::getLocale()));
    }
}
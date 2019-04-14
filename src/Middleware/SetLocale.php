<?php


namespace Eslym\EasyLocalize\Middleware;


use Closure;
use Eslym\EasyLocalize\Facades\Localize;
use Illuminate\Http\Response;

class SetLocale
{
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
                return redirect()->to(Localize::to($to));
            } else {
                $lang = Localize::current();
            }
        }
        app()->setLocale($lang);
        /** @var Response $response */
        $response = $next($request);
        return $response->withCookie(cookie()->forever('language', app()->getLocale()));
    }
}
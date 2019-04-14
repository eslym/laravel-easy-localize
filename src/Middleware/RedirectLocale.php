<?php


namespace Eslym\EasyLocalize\Middleware;


use Closure;
use Eslym\EasyLocalize\Facades\Localize;

class RedirectLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->isMethod('GET')){
            return $next($request);
        }
        return redirect()->to(Localize::to(Localize::current()));
    }
}
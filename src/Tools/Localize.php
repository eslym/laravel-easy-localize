<?php


namespace Eslym\EasyLocalize\Tools;


use Closure;
use Eslym\EasyLocalize\Contracts\Localize as LocalizeContract;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class Localize implements LocalizeContract
{

    protected $available = [];

    protected $accepts = [];

    protected $alias = [];

    protected $pattern = '';

    public function __construct(array $available)
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
    }

    /**
     * @param Closure $routes
     */
    public function routes(Closure $routes): void
    {
        Route::middleware('locale-redirect')
            ->group($routes);
        foreach ($this->accepts as $lang){
            Route::prefix($lang)->name("$lang.")
                ->middleware("locale-load:$lang")
                ->group($routes);
        }
    }

    /**
     * @param string|string[] $language
     * @return string|string[]
     */
    public function to($language)
    {
        $uri = request()->getRequestUri();
        $original = ltrim(preg_replace($this->pattern, '', $uri), '/');
        if(is_string($language)){
            $language = urlencode($language);
            return url("$language/$original");
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
        $request = request();
        return $request->cookie('language', $request->getPreferredLanguage($this->accepts) ?? config('app.locale'));
    }

    /**
     * @param string $language
     * @return string
     */
    public function name(string $language): string
    {
        return config("localize.names.$language", $language);
    }
}
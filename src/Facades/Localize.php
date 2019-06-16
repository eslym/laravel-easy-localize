<?php


namespace Eslym\EasyLocalize\Facades;


use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * Class Localize
 * @package Eslym\EasyLocalize\Facades
 *
 * @method static void routes(Closure $routes)
 * @method static string|string[] to(string|string[] $language, string $uri = null)
 * @method static string[] accepts()
 * @method static string[] available()
 * @method static array[] aliases()
 * @method static string current()
 * @method static string|array name(string $language = null)
 * @method static ?array setting(string $language = null)
 */
class Localize extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'localize';
    }
}
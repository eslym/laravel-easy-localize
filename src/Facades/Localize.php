<?php


namespace Eslym\EasyLocalize\Facades;


use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * Class Localize
 * @package Eslym\EasyLocalize\Facades
 *
 * @method static void routes(Closure $routes)
 * @method static string|string[] to(string|string[] $language)
 * @method static string[] accepts()
 * @method static string[] available()
 * @method static array[] aliases()
 * @method static string current()
 * @method static string name(string $language)
 */
class Localize extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'localize';
    }
}
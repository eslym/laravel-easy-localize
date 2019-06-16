<?php


namespace Eslym\EasyLocalize\Contracts;


use Closure;

interface Localize
{
    /**
     * @param Closure $routes
     */
    public function routes(Closure $routes): void;

    /**
     * @param string|string[] $language
     * @param string $uri
     * @return string|string[]
     */
    public function to($language, $uri = null);

    /**
     * @return string[]
     */
    public function accepts(): array;

    /**
     * @return string[]
     */
    public function available(): array;

    /**
     * @return array[]
     */
    public function aliases(): array;

    /**
     * @return string
     */
    public function current(): string;

    /**
     * @param string|null $language
     * @return string|null
     */
    public function name(?string $language = null);

    /**
     * @param string|null $language
     * @return array|null
     */
    public function setting(?string $language = null): ?array;
}
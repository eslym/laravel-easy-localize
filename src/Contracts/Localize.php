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
     * @return string|string[]
     */
    public function to($language);

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
     * @param string $language
     * @return string
     */
    public function name(string $language): string;
}
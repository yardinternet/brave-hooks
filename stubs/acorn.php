<?php

declare(strict_types=1);

/**
 * Global helpers Acorn loads at runtime from its Illuminate/Foundation/helpers.php, used by PHPStan only.
 * That file can't be scanned directly: it also declares Laravel's __(), which would shadow WordPress' __().
 */

/**
 * @template TClass of object
 *
 * @param string|class-string<TClass>|null $abstract
 * @param array<string, mixed> $parameters
 *
 * @return ($abstract is class-string<TClass> ? TClass : ($abstract is null ? \Roots\Acorn\Application : mixed))
 */
function app($abstract = null, array $parameters = [])
{
}

/**
 * @param array<string, mixed>|string|null $key
 * @param mixed $default
 *
 * @return ($key is null ? \Illuminate\Config\Repository : ($key is string ? mixed : null))
 */
function config($key = null, $default = null)
{
}

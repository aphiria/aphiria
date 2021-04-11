<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ExtensionMethods;

use Closure;
use ReflectionObject;

/**
 * Defines the class to register extensions to
 */
final class ExtensionMethodRegistry
{
    /** @var array<class-string, array<string, Closure>> The mapping of interfaces to extension methods */
    private static array $extensionsByInterface = [];
    /** @var array<class-string, array<string, Closure|null>> The memoized mapping of class names to extension methods/null if none was registered */
    private static array $memoizedExtensionsByClass = [];

    /**
     * Gets the extension method as a closure
     *
     * @param object $object The object we're calling an extension method on
     * @param string $method The name of the method we're calling
     * @param list<mixed> $args The list of arguments to pass in
     * @return Closure|null The closure if there was one, otherwise null
     */
    public static function getExtensionMethod(object $object, string $method, array $args = []): ?Closure
    {
        $closure = null;

        // Check to see if a closure (or null, if none was found on a previous call) was already stored
        if (
            isset(self::$memoizedExtensionsByClass[$object::class])
            && \array_key_exists($method, self::$memoizedExtensionsByClass[$object::class])
        ) {
            $closure = self::$memoizedExtensionsByClass[$object::class][$method];
        } else {
            self::$memoizedExtensionsByClass[$object::class] = [];
            $interfaces = [$object::class, ...(new ReflectionObject($object))->getInterfaceNames()];

            foreach ($interfaces as $interface) {
                if (isset(self::$extensionsByInterface[$interface][$method])) {
                    $closure = self::$memoizedExtensionsByClass[$object::class][$method] = self::$extensionsByInterface[$interface][$method];
                    break;
                }
            }

            self::$memoizedExtensionsByClass[$object::class][$method] = $closure;
        }

        return $closure;
    }

    /**
     * Registers an extension method
     *
     * @param class-string|list<class-string> $interfaces The interface or list of interfaces to register an extension method for
     * @param string $method The name of the extension method
     * @param Closure $closure The closure that will be invoked whenever the extension method will be called
     */
    public static function registerExtensionMethod(string|array $interfaces, string $method, Closure $closure): void
    {
        foreach ((array)$interfaces as $interface) {
            if (!isset(self::$extensionsByInterface[$interface])) {
                self::$extensionsByInterface[$interface] = [];
            }

            self::$extensionsByInterface[$interface][$method] = $closure;
        }
    }
}

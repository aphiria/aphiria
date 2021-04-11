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
    private static array $extensionMethodsByInterface = [];
    /** @var array<class-string, array<string, Closure|null>> The memoized mapping of class names to extension methods/null if none was registered */
    private static array $memoizedExtensionMethodsByClass = [];

    /**
     * Gets the extension method as a closure
     *
     * @param object $object The object we're calling an extension method on
     * @param string $method The name of the method we're calling
     * @return Closure|null The extension method as a closure if there was one, otherwise null
     */
    public static function getExtensionMethod(object $object, string $method): ?Closure
    {
        $closure = null;

        // Check to see if a closure (or null, if none was found on a previous call) was already stored
        if (
            isset(self::$memoizedExtensionMethodsByClass[$object::class])
            && \array_key_exists($method, self::$memoizedExtensionMethodsByClass[$object::class])
        ) {
            $closure = self::$memoizedExtensionMethodsByClass[$object::class][$method];
        } else {
            self::$memoizedExtensionMethodsByClass[$object::class] = [];
            $interfaces = [$object::class, ...(new ReflectionObject($object))->getInterfaceNames()];

            foreach ($interfaces as $interface) {
                if (isset(self::$extensionMethodsByInterface[$interface][$method])) {
                    $closure = self::$memoizedExtensionMethodsByClass[$object::class][$method] = self::$extensionMethodsByInterface[$interface][$method];
                    break;
                }
            }

            self::$memoizedExtensionMethodsByClass[$object::class][$method] = $closure;
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
            if (!isset(self::$extensionMethodsByInterface[$interface])) {
                self::$extensionMethodsByInterface[$interface] = [];
            }

            self::$extensionMethodsByInterface[$interface][$method] = $closure;
        }
    }
}

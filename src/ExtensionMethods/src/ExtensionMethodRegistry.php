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

/**
 * Defines the class to register extension methods to
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
     * @param string $methodName The name of the extension method we want
     * @return Closure|null The extension method as a closure if there was one, otherwise null
     */
    public static function getExtensionMethod(object $object, string $methodName): ?Closure
    {
        $closure = null;

        // Check to see if a closure (or null, if none was found on a previous call) was already stored
        if (
            isset(self::$memoizedExtensionMethodsByClass[$object::class])
            && \array_key_exists($methodName, self::$memoizedExtensionMethodsByClass[$object::class])
        ) {
            $closure = self::$memoizedExtensionMethodsByClass[$object::class][$methodName];
        } else {
            self::$memoizedExtensionMethodsByClass[$object::class] = [];
            // To avoid unnecessary calls to get interfaces/parent classes, wrap their evaluation in closures
            $interfaceCallbacks = [
                fn (): array => [$object::class],
                fn (): array => \array_values(\class_parents($object)),
                fn (): array => \array_values(\class_implements($object))
            ];

            foreach ($interfaceCallbacks as $interfaceCallback) {
                foreach ($interfaceCallback() as $interface) {
                    if (isset(self::$extensionMethodsByInterface[$interface][$methodName])) {
                        $closure = self::$memoizedExtensionMethodsByClass[$object::class][$methodName] = self::$extensionMethodsByInterface[$interface][$methodName];
                        break;
                    }
                }
            }

            self::$memoizedExtensionMethodsByClass[$object::class][$methodName] = $closure;
        }

        return $closure;
    }

    /**
     * Registers an extension method
     *
     * @param class-string|list<class-string> $interfaces The interface or list of interfaces to register an extension method for
     * @param string $methodName The name of the extension method
     * @param Closure $closure The closure that will be invoked whenever the extension method will be called
     */
    public static function registerExtensionMethod(string|array $interfaces, string $methodName, Closure $closure): void
    {
        foreach ((array)$interfaces as $interface) {
            if (!isset(self::$extensionMethodsByInterface[$interface])) {
                self::$extensionMethodsByInterface[$interface] = [];
            }

            self::$extensionMethodsByInterface[$interface][$methodName] = $closure;
        }
    }
}

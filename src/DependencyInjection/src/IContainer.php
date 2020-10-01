<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Closure;

/**
 * Defines the interface for dependency injection containers to implement
 */
interface IContainer extends IServiceResolver
{
    /**
     * Binds a class to use whenever resolving an interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param string $concreteClass The concrete class to bind to the interface
     * @param array $primitives The list of primitives to inject (must be in same order they appear in constructor),
     * @param bool $resolveAsSingleton Whether or not to resolve the class as a singleton
     */
    public function bindClass(
        string|array $interfaces,
        string $concreteClass,
        array $primitives = [],
        bool $resolveAsSingleton = false
    ): void;

    /**
     * Binds a factory that will return a concrete instance of the interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param callable $factory The factory to bind
     * @param bool $resolveAsSingleton Whether or not to resolve the factory as a singleton
     */
    public function bindFactory(string|array $interfaces, callable $factory, bool $resolveAsSingleton = false): void;

    /**
     * Binds a concrete instance to the interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param object $instance The instance to bind
     */
    public function bindInstance(string|array $interfaces, object $instance): void;

    /**
     * Resolves a closure's parameters and calls it
     *
     * @param Closure $closure The closure to resolve
     * @param array $primitives The list of primitives to inject (must be in same order they appear in closure)
     * @return mixed The result of the call
     * @throws CallException Thrown if there was an error calling the method
     */
    public function callClosure(Closure $closure, array $primitives = []): mixed;

    /**
     * Resolves a method's parameters and calls it
     *
     * @param object|string $instance The instance (or class name if the method is static) whose method we're calling
     * @param string $methodName The name of the method we're calling
     * @param array $primitives The list of primitives to inject (must be in same order they appear in closure)
     * @param bool $ignoreMissingMethod Whether or not we ignore if the method does not exist
     * @return mixed The result of the call
     * @throws CallException Thrown if there was an error calling the method
     */
    public function callMethod(
        object|string $instance,
        string $methodName,
        array $primitives = [],
        bool $ignoreMissingMethod = false
    ): mixed;

    /**
     * Gets whether or not an interface has a binding
     *
     * @param string $interface The interface to check
     * @return bool True if the interface has a binding, otherwise false
     */
    public function hasBinding(string $interface): bool;

    /**
     * Unbinds the interface from the container
     *
     * @param string|array $interfaces The interface or interfaces to unbind from
     */
    public function unbind(string|array $interfaces): void;
}

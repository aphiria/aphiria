<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use InvalidArgumentException;

/**
 * Defines the interface for dependency injection containers to implement
 */
interface IContainer extends IServiceResolver
{
    /**
     * Binds a factory that will return a concrete instance of the interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param callable $factory The factory to bind
     * @param bool $resolveAsSingleton Whether or not to resolve the factory as a singleton
     */
    public function bindFactory($interfaces, callable $factory, bool $resolveAsSingleton = false): void;

    /**
     * Binds a concrete instance to the interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param object $instance The instance to bind
     */
    public function bindInstance($interfaces, object $instance): void;

    /**
     * Binds a non-singleton concrete class to an interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param string|null $concreteClass The concrete class to bind, or null if the interface actually is a concrete class
     * @param array $primitives The list of primitives to inject (must be in same order they appear in constructor)
     */
    public function bindPrototype($interfaces, string $concreteClass = null, array $primitives = []): void;

    /**
     * Binds a singleton concrete class to an interface
     *
     * @param string|array $interfaces The interface or interfaces to bind to
     * @param string|null $concreteClass The concrete class to bind, or null if the interface actually is a concrete class
     * @param array $primitives The list of primitives to inject (must be in same order they appear in constructor)
     */
    public function bindSingleton($interfaces, string $concreteClass = null, array $primitives = []): void;

    /**
     * Resolves a closure's parameters and calls it
     *
     * @param callable $closure The closure to resolve
     * @param array $primitives The list of primitives to inject (must be in same order they appear in closure)
     * @return mixed The result of the call
     * @throws CallException Thrown if there was an error calling the method
     */
    public function callClosure(callable $closure, array $primitives = []);

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
        $instance,
        string $methodName,
        array $primitives = [],
        bool $ignoreMissingMethod = false
    );

    /**
     * Sets a context for all calls in the callback
     *
     * @param Context|string $context The context (or name of the target class) to apply to all bindings and resolutions
     * @param callable $callback The callback that takes in an IContainer and performs bindings/resolutions under the context
     * @throws InvalidArgumentException Thrown if the context was not of the correct type
     */
    public function for($context, callable $callback);

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
    public function unbind($interfaces): void;
}

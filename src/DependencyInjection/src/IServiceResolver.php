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

/**
 * Defines the interface for service resolvers to implement
 */
interface IServiceResolver
{
    /**
     * Sets a context for all calls in the callback
     *
     * @template T
     * @param Context|class-string $context The context (or name of the target class) to apply to all bindings and resolutions
     * @param callable(static): T $callback The callback that takes in an instance of the implementing resolver and performs actions under the context
     * @return T The return value of the callback
     */
    public function for(Context|string $context, callable $callback);

    /**
     * Resolve an instance of the interface
     *
     * @template T
     * @param class-string<T> $interface The interface to resolve
     * @return T The resolved instance
     * @throws ResolutionException Thrown if there was an error resolving the interface
     */
    public function resolve(string $interface): object;

    /**
     * Tries to resolve an instance of the interface
     *
     * @template T
     * @param class-string<T> $interface The interface to resolve
     * @param-out T $instance The resolved instance if successful
     * @return bool True if the binding was successful, otherwise false
     */
    public function tryResolve(string $interface, ?object &$instance): bool;
}

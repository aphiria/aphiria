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

/**
 * Defines the interface for dependency resolvers to implement
 */
interface IDependencyResolver
{
    /**
     * Resolve an instance of the interface
     *
     * @param string $interface The interface to resolve
     * @return object The resolved instance
     * @throws ResolutionException Thrown if there was an error resolving the interface
     */
    public function resolve(string $interface): object;

    /**
     * Tries to resolve an instance of the interface
     *
     * @param string $interface The interface to resolve
     * @param object|null $instance The resolved instance if successful
     * @return bool True if the binding was successful, otherwise false
     */
    public function tryResolve(string $interface, ?object &$instance): bool;
}

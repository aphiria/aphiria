<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers;

use RuntimeException;

/**
 * Defines the interface for service resolvers to implement
 */
interface IServiceResolver
{
    /**
     * Resolves a class instance
     *
     * @template T of object
     * @param class-string<T> $className The fully-qualified name of the class to resolve
     * @return T The resolved instance
     * @throws RuntimeException Thrown if the instance could not be resolved
     * @note This can only resolve the following types in the class' constructor:  IComponent, IContainer, IServiceResolver, Container, and IApplicationBuilder
     */
    public function resolve(string $className): object;
}

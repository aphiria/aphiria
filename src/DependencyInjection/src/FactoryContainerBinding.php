<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Closure;

/**
 * Defines a factory container binding
 * @template T of object
 * @implements IContainerBinding<T>
 * @internal
 */
readonly class FactoryContainerBinding implements IContainerBinding
{
    /**
     * @param Closure(): T $factory The factory
     * @param bool $resolveAsSingleton Whether or not the factory should be resolved as a singleton
     */
    public function __construct(public Closure $factory, private bool $resolveAsSingleton)
    {
    }

    /**
     * Gets whether or not to resolve as a singleton
     *
     * @return bool Whether or not to resolve as a singleton
     */
    public function resolveAsSingleton(): bool
    {
        return $this->resolveAsSingleton;
    }
}

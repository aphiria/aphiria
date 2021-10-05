<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Closure;

/**
 * Defines a factory container binding
 * @internal
 */
class FactoryContainerBinding implements IContainerBinding
{
    /**
     * @param Closure(): object $factory The factory
     * @param bool $resolveAsSingleton Whether or not the factory should be resolved as a singleton
     */
    public function __construct(public readonly Closure $factory, private readonly bool $resolveAsSingleton)
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

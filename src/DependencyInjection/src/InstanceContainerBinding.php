<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines an instance container binding
 * @template T of object
 * @implements IContainerBinding<T>
 * @internal
 */
class InstanceContainerBinding implements IContainerBinding
{
    /**
     * @param T $instance The instance
     */
    public function __construct(public readonly object $instance)
    {
    }

    /**
     * Gets whether or not to resolve as a singleton
     *
     * @return bool Whether or not to resolve as a singleton
     */
    public function resolveAsSingleton(): bool
    {
        return true;
    }
}

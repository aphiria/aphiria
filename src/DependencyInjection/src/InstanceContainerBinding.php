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

/**
 * Defines an instance container binding
 * @template T of object
 * @implements IContainerBinding<T>
 * @internal
 */
readonly class InstanceContainerBinding implements IContainerBinding
{
    public bool $resolveAsSingleton;

    /**
     * @param T $instance The instance
     */
    public function __construct(public object $instance)
    {
        $this->resolveAsSingleton = true;
    }
}

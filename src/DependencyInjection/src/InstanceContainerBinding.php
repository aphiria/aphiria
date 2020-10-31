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
 * Defines an instance container binding
 * @internal
 */
class InstanceContainerBinding implements IContainerBinding
{
    /**
     * @param object $instance The instance
     */
    public function __construct(private object $instance)
    {
    }

    /**
     * Gets the instance
     *
     * @return object The instance
     */
    public function getInstance(): object
    {
        return $this->instance;
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

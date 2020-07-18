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
    /** @var object The instance */
    private object $instance;

    /**
     * @param object $instance The instance
     */
    public function __construct(object $instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return object
     */
    public function getInstance(): object
    {
        return $this->instance;
    }

    /**
     * @return bool
     */
    public function resolveAsSingleton(): bool
    {
        return true;
    }
}

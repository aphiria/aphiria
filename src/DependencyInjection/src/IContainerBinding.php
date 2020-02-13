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
 * Defines the interface for container bindings to implement
 * @internal
 */
interface IContainerBinding
{
    /**
     * Gets whether or not this binding should be resolved as a singleton
     *
     * @return bool True if the binding should be resolved as a singleton, otherwise false
     */
    public function resolveAsSingleton(): bool;
}

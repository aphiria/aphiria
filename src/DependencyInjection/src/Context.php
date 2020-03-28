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
 * Defines the context that resolutions and binders occur in
 */
abstract class Context
{
    /**
     * Gets the targeted class
     *
     * @return string|null The targeted class, if there was one
     */
    abstract public function getTargetClass(): ?string;

    /**
     * Whether or not the context is targeted
     *
     * @return bool True if the context is targeted, otherwise false
     */
    abstract public function isTargeted(): bool;

    /**
     * Gets whether or not the context is universal
     *
     * @return bool True if the context is universal, otherwise false
     */
    abstract public function isUniversal(): bool;
}

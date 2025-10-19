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

/**
 * Defines the interface for component builders to implement
 */
interface IComponentBuilder
{
    /**
     * @param list<DiscoveredComponent> $components The components to build
     */
    public function build(array $components): void;
}

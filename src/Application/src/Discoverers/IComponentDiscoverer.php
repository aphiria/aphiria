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

use ReflectionClass;

interface IComponentDiscoverer
{
    /**
     * Discovers all components in the input class
     *
     * @param ReflectionClass $class The class to discover components in
     * @return list<DiscoveredComponent> The list of discovered components
     */
    public function discover(ReflectionClass $class): array;
}

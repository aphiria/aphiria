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

/**
 * Defines the interface for component discoverers to implement
 */
interface IComponentDiscoverer
{
    /** @var string The name of the component being discovered, which must match the corresponding component builder's component name */
    public string $componentName { get; }

    /**
     * Discovers all components in the input class
     *
     * @param ReflectionClass $class The class to discover components in
     * @return list<DiscoveredComponent> The list of discovered components
     */
    public function discover(ReflectionClass $class): array;
}

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
 * Defines the discoverer of discoverers
 */
final class DiscovererDiscoverer implements IComponentDiscoverer
{
    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        if ($class->getName() === self::class || !$class->implementsInterface(IComponentDiscoverer::class)) {
            return [];
        }

        return [new DiscoveredComponent($class)];
    }
}

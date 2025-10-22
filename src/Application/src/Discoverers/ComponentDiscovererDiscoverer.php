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
 * Defines the discoverer of component discoverers
 */
final class ComponentDiscovererDiscoverer implements IComponentDiscoverer
{
    public string $componentName {
        get => 'aphiria:discoverers';
    }

    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        if ($class->getName() === self::class || !$class->implementsInterface(IComponentDiscoverer::class)) {
            return [];
        }

        return [new DiscoveredComponent($this->componentName, $class)];
    }
}

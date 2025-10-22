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
 * Defines the discoverer for component builders
 */
final class ComponentBuilderDiscoverer implements IComponentDiscoverer
{
    public string $componentName {
        get => 'aphiria:discoverers';
    }

    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        if ($class->getName() === self::class || !$class->implementsInterface(IComponentBuilder::class)) {
            return [];
        }

        return [new DiscoveredComponent($this->componentName, $class)];
    }
}

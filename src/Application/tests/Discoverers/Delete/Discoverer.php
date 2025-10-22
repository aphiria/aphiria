<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers\Delete;

use Aphiria\Application\Discoverers\DiscoveredComponent;
use Aphiria\Application\Discoverers\IComponentDiscoverer;
use ReflectionClass;

class Discoverer implements IComponentDiscoverer
{
    public string $componentName {
        get => 'app:foo';
    }

    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        // Do  some dummy filtering
        if ($class->name !== SomeClass::class) {
            return [];
        }

        return [new DiscoveredComponent($this->componentName, $class)];
    }
}

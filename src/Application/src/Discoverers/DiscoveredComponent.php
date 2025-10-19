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

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Defines a discovered component
 */
class DiscoveredComponent
{
    /**
     * @param ReflectionClass $class The class that was discovered
     * @param ReflectionMethod|null $method The method that was discovered if there was one, otherwise null
     * @param ReflectionProperty|null $property The property that was discovered if there was one, otherwise null
     * @param ReflectionAttribute|null $attribute The attribute that was discovered if there was one, otherwise null
     * @param list<DiscoveredComponent> $siblingComponents The sibling components that were discovered under this component, if any
     * @param list<DiscoveredComponent> $childComponents The child components that were discovered under this component, if any
     */
    public function __construct(
        public protected(set) ReflectionClass $class,
        public protected(set) ?ReflectionMethod $method = null,
        public protected(set) ?ReflectionProperty $property = null,
        public protected(set) ?ReflectionAttribute $attribute = null,
        public protected(set) ?DiscoveredComponent $parentComponent = null,
        public array $siblingComponents = [],
        public array $childComponents = [],
    ) {}
}

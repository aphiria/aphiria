<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers;

use Aphiria\Application\Discoverers\DiscoveredComponent;
use Aphiria\Application\Tests\Discoverers\Mocks\DiscoveredClass;
use Aphiria\Application\Tests\Discoverers\Mocks\SomeAttribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

class DiscoveredComponentTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $discoveredClass = new DiscoveredClass();
        $expectedClass = new ReflectionClass($discoveredClass::class);
        $expectedMethod = new ReflectionMethod($discoveredClass, 'someMethod');
        $expectedProperty = new ReflectionProperty($discoveredClass, 'someProperty');
        $expectedParentComponent = new DiscoveredComponent(new ReflectionClass($discoveredClass::class));
        $expectedSiblingComponents = [new DiscoveredComponent(new ReflectionClass($discoveredClass::class))];
        $expectedChildComponents = [new DiscoveredComponent(new ReflectionClass($discoveredClass::class))];
        $attribute = new ReflectionObject($discoveredClass)
            ->getAttributes(SomeAttribute::class)[0] ?? null;
        $discoveredComponent = new DiscoveredComponent(
            $expectedClass,
            $expectedMethod,
            $expectedProperty,
            $attribute,
            $expectedParentComponent,
            $expectedSiblingComponents,
            $expectedChildComponents,
        );
        $this->assertSame($expectedClass, $discoveredComponent->class);
        $this->assertSame($expectedMethod, $discoveredComponent->method);
        $this->assertSame($expectedProperty, $discoveredComponent->property);
        $this->assertSame($attribute, $discoveredComponent->attribute);
        $this->assertSame($expectedParentComponent, $discoveredComponent->parentComponent);
        $this->assertSame($expectedSiblingComponents, $discoveredComponent->siblingComponents);
        $this->assertSame($expectedChildComponents, $discoveredComponent->childComponents);
    }
}

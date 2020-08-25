<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\RouteConstraint;
use Aphiria\Routing\Annotations\RouteGroup;
use PHPUnit\Framework\TestCase;

class RouteGroupTest extends TestCase
{
    public function testDefaultValuesOfRoutePropertiesAreSet(): void
    {
        $routeGroup = new RouteGroup([]);
        $this->assertSame('', $routeGroup->path);
        $this->assertNull($routeGroup->host);
        $this->assertFalse($routeGroup->isHttpsOnly);
        $this->assertEquals([], $routeGroup->attributes);
        $this->assertEquals([], $routeGroup->constraints);
    }

    public function testPathCanBeSetViaPath(): void
    {
        $routeGroup = new RouteGroup(['path' => '/foo']);
        $this->assertSame('/foo', $routeGroup->path);
    }

    public function testPathCanBeSetViaValue(): void
    {
        $routeGroup = new RouteGroup(['value' => '/foo']);
        $this->assertSame('/foo', $routeGroup->path);
    }

    public function testPropertiesAreSetViaConstructor(): void
    {
        $routeGroup = new RouteGroup([
            'path' => '/foo',
            'host' => 'example.com',
            'isHttpsOnly' => true,
            'attributes' => ['attr' => 'val'],
            'constraints' => [new RouteConstraint(['className' => 'constraintClass', 'constructorParams' => ['param']])]
        ]);
        $this->assertSame('/foo', $routeGroup->path);
        $this->assertSame('example.com', $routeGroup->host);
        $this->assertTrue($routeGroup->isHttpsOnly);
        $this->assertEquals(['attr' => 'val'], $routeGroup->attributes);
        $this->assertCount(1, $routeGroup->constraints);
        $this->assertSame('constraintClass', $routeGroup->constraints[0]->className);
        $this->assertEquals(['param'], $routeGroup->constraints[0]->constructorParams);
    }
}

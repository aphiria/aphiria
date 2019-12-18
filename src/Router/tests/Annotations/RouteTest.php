<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\Route;
use Aphiria\Routing\Annotations\RouteConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route annotation
 */
class RouteTest extends TestCase
{
    public function testDefaultValuesOfRoutePropertiesAreSet(): void
    {
        $route = new Route([]);
        $this->assertEquals('', $route->path);
        $this->assertEquals([], $route->httpMethods);
        $this->assertNull($route->host);
        $this->assertNull($route->name);
        $this->assertFalse($route->isHttpsOnly);
        $this->assertEquals([], $route->attributes);
        $this->assertEquals([], $route->constraints);
    }

    public function testPathCanBeSetViaPath(): void
    {
        $route = new Route(['path' => '/foo']);
        $this->assertEquals('/foo', $route->path);
    }

    public function testPathCanBeSetViaValue(): void
    {
        $route = new Route(['value' => '/foo']);
        $this->assertEquals('/foo', $route->path);
    }

    public function testPropertiesAreSetViaConstructor(): void
    {
        $route = new Route([
            'path' => '/foo',
            'httpMethods' => ['GET'],
            'host' => 'example.com',
            'name' => 'dave',
            'isHttpsOnly' => true,
            'attributes' => ['attr' => 'val'],
            'constraints' => [new RouteConstraint(['className' => 'constraintClass', 'constructorParams' => ['param']])]
        ]);
        $this->assertEquals('/foo', $route->path);
        $this->assertEquals(['GET'], $route->httpMethods);
        $this->assertEquals('example.com', $route->host);
        $this->assertEquals('dave', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertEquals(['attr' => 'val'], $route->attributes);
        $this->assertCount(1, $route->constraints);
        $this->assertEquals('constraintClass', $route->constraints[0]->className);
        $this->assertEquals(['param'], $route->constraints[0]->constructorParams);
    }
}

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

use Aphiria\Routing\Annotations\Route;
use Aphiria\Routing\Annotations\RouteConstraint;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testDefaultValuesOfRoutePropertiesAreSet(): void
    {
        $route = new Route([]);
        $this->assertSame('', $route->path);
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
        $this->assertSame('/foo', $route->path);
    }

    public function testPathCanBeSetViaValue(): void
    {
        $route = new Route(['value' => '/foo']);
        $this->assertSame('/foo', $route->path);
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
        $this->assertSame('/foo', $route->path);
        $this->assertEquals(['GET'], $route->httpMethods);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('dave', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertEquals(['attr' => 'val'], $route->attributes);
        $this->assertCount(1, $route->constraints);
        $this->assertSame('constraintClass', $route->constraints[0]->className);
        $this->assertEquals(['param'], $route->constraints[0]->constructorParams);
    }
}

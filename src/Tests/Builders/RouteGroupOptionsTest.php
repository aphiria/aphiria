<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Builders;

use Opulence\Routing\Builders\RouteGroupOptions;
use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Tests the route group options
 */
class RouteGroupOptionsTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteGroupOptions The options to use in tests */
    private $routeGroupOptions;
    /** @var MiddlewareBinding[] The list of middleware bindings in the options */
    private $middlewareBindings = [];

    public function setUp(): void
    {
        $this->middlewareBindings = [new MiddlewareBinding('foo')];
        $this->routeGroupOptions = new RouteGroupOptions(
            'path',
            'host',
            true,
            $this->middlewareBindings,
            ['foo' => 'bar']
        );
    }

    public function testCorrectAttributesAreReturned(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->routeGroupOptions->getAttributes());
    }

    public function testCorrectHostIsReturned(): void
    {
        $this->assertEquals('host', $this->routeGroupOptions->getHostTemplate());
    }

    public function testCorrectHttpsOnlySettingIsReturned(): void
    {
        $this->assertTrue($this->routeGroupOptions->isHttpsOnly());
    }

    public function testCorrectMiddlewareBindingsAreReturned(): void
    {
        $this->assertEquals($this->middlewareBindings, $this->routeGroupOptions->getMiddlewareBindings());
    }

    public function testCorrectPathIsReturned(): void
    {
        $this->assertEquals('path', $this->routeGroupOptions->getPathTemplate());
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Framework\Routing\Builders\RouterBuilderProxy;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the router builder proxy
 */
class RouterBuilderProxyTest extends TestCase
{
    private RouterBuilderProxy $routerBuilderProxy;
    /** @var RouterBuilder|MockObject */
    private RouterBuilder $routerBuilder;

    protected function setUp(): void
    {
        $this->routerBuilder = $this->createMock(RouterBuilder::class);
        $this->routerBuilderProxy = new RouterBuilderProxy(
            fn () => $this->routerBuilder
        );
    }

    public function testBuildRegistersRoutesToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedCallback = fn (RouteBuilderRegistry $routeBuilders) => $routeBuilders->get('/foo')->toMethod('Foo', 'bar');
        $this->routerBuilder->expects($this->at(0))
            ->method('withRoutes')
            ->with($expectedCallback);
        $this->routerBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->routerBuilderProxy->withRoutes($expectedCallback);
        $this->routerBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildWithAnnotationsConfiguresProxiedComponentBuilderToUseAnnotations(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $this->routerBuilder->expects($this->at(0))
            ->method('withAnnotations');
        $this->routerBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->routerBuilderProxy->withAnnotations();
        $this->routerBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(RouterBuilder::class, $this->routerBuilderProxy->getProxiedType());
    }
}

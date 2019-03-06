<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration\Tests\Http;

use Aphiria\Configuration\Http\HttpApplicationBuilder;
use Aphiria\Configuration\Http\IHttpModuleBuilder;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Defines the HTTP application builder tests
 */
class HttpApplicationBuilderTest extends TestCase
{
    /** @var RouteBuilderRegistry */
    private $routes;
    /** @var IBootstrapperRegistry|MockObject */
    private $bootstrappers;
    /** @var HttpApplicationBuilder */
    private $appBuilder;

    protected function setUp(): void
    {
        $this->routes = new RouteBuilderRegistry();
        $this->bootstrappers = $this->createMock(IBootstrapperRegistry::class);
        $this->appBuilder = new HttpApplicationBuilder($this->routes, $this->bootstrappers);
    }

    public function testBootstrapperDelegatesAreInvokedWithRegistryOnBuild(): void
    {
        $isInvoked = false;
        $this->appBuilder->withBootstrappers(function (IBootstrapperRegistry $bootstrappers) use (&$isInvoked) {
            $this->assertSame($this->bootstrappers, $bootstrappers);
            $isInvoked = true;
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testRouteDelegatesAreInvokedWithRegistryOnBuild(): void
    {
        $isInvoked = false;
        $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) use (&$isInvoked) {
            $this->assertSame($this->routes, $routes);
            $isInvoked = true;
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testWithBootstrappersReturnsSelf(): void
    {
        $this->assertSame(
            $this->appBuilder,
            $this->appBuilder->withBootstrappers(function (IBootstrapperRegistry $bootstrappers) {
                // Don't do anything
            })
        );
    }

    public function testWithModuleBuildsTheModule(): void
    {
        /** @var IHttpModuleBuilder|MockObject $module */
        $module = $this->createMock(IHttpModuleBuilder::class);
        $module->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModule($module);
    }

    public function testWithRoutesReturnsSelf(): void
    {
        $this->assertSame(
            $this->appBuilder,
            $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) {
                // Don't do anything
            })
        );
    }
}

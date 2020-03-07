<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Middleware\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilderProxy;
use Aphiria\Middleware\MiddlewareBinding;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware builder proxy
 */
class MiddlewareBuilderProxyTest extends TestCase
{
    private MiddlewareBuilderProxy $middlewareBuilderProxy;
    /** @var MiddlewareBuilder|MockObject */
    private MiddlewareBuilder $middlewareBuilder;

    protected function setUp(): void
    {
        $this->middlewareBuilder = $this->createMock(MiddlewareBuilder::class);
        $this->middlewareBuilderProxy = new MiddlewareBuilderProxy(
            fn () => $this->middlewareBuilder
        );
    }

    public function testBuildRegistersMultipleGlobalMiddlewareToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedMiddlewareBinding1 = new MiddlewareBinding('foo');
        $expectedMiddlewareBinding2 = new MiddlewareBinding('bar');
        $this->middlewareBuilder->expects($this->at(0))
            ->method('withGlobalMiddleware')
            ->with([$expectedMiddlewareBinding1, $expectedMiddlewareBinding2]);
        $this->middlewareBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->middlewareBuilderProxy->withGlobalMiddleware([$expectedMiddlewareBinding1, $expectedMiddlewareBinding2]);
        $this->middlewareBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildRegistersSingleGlobalMiddlewareToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedMiddlewareBinding = new MiddlewareBinding('foo');
        $this->middlewareBuilder->expects($this->at(0))
            ->method('withGlobalMiddleware')
            ->with($expectedMiddlewareBinding);
        $this->middlewareBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->middlewareBuilderProxy->withGlobalMiddleware($expectedMiddlewareBinding);
        $this->middlewareBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(MiddlewareBuilder::class, $this->middlewareBuilderProxy->getProxiedType());
    }
}

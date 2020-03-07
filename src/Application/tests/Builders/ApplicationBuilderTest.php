<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Builders;

use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IComponentBuilder;
use Aphiria\Application\Builders\IComponentBuilderProxy;
use Aphiria\Application\Builders\IModuleBuilder;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the application builder
 */
class ApplicationBuilderTest extends TestCase
{
    private ApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        $this->appBuilder = new class() extends ApplicationBuilder
        {
            public function build(): object
            {
                $this->buildModules();
                $this->buildComponents();

                return $this;
            }
        };
    }

    public function testComponentsAreBuiltInPriorityDescendingOrder(): void
    {
        /**
         * I need to basically duplicate the class definitions here so that each has a unique class name.
         * When I build those components, I'm adding them to an array so that I can check the build order.
         */
        $builtComponentBuilders = [];
        $lowPriorityComponentBuilder = new class($builtComponentBuilders) implements IComponentBuilder
        {
            private array $builtComponentsBuilders;

            public function __construct(array &$builtComponentBuilders)
            {
                $this->builtComponentsBuilders = &$builtComponentBuilders;
            }

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->builtComponentsBuilders[] = $this;
            }
        };
        $highPriorityComponentBuilder = new class($builtComponentBuilders) implements IComponentBuilder
        {
            private array $builtComponentsBuilders;

            public function __construct(array &$builtComponentBuilders)
            {
                $this->builtComponentsBuilders = &$builtComponentBuilders;
            }

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->builtComponentsBuilders[] = $this;
            }
        };
        $this->appBuilder->withComponentBuilder($lowPriorityComponentBuilder, 2);
        $this->appBuilder->withComponentBuilder($highPriorityComponentBuilder, 1);
        $this->appBuilder->build();
        $this->assertEquals([$highPriorityComponentBuilder, $lowPriorityComponentBuilder], $builtComponentBuilders);
    }

    public function testComponentsAreBuiltOnBuild(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilder::class);
        $componentBuilder->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->appBuilder->build();
    }

    public function testModulesAreBuiltOnBuild(): void
    {
        $moduleBuilder = $this->createMock(IModuleBuilder::class);
        $moduleBuilder->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModuleBuilder($moduleBuilder);
        $this->appBuilder->build();
    }

    public function testHasComponentBuilderReturnsWhetherOrNotBuilderIsRegistered(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilder::class);
        $this->assertFalse($this->appBuilder->hasComponentBuilder(\get_class($componentBuilder)));
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->assertTrue($this->appBuilder->hasComponentBuilder(\get_class($componentBuilder)));
    }

    public function testHasComponentBuilderReturnsWhetherOrNotBuilderIsRegisteredWhenUsingProxiedBuilder(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilderProxy::class);
        $componentBuilder->method('getProxiedType')
            ->willReturn('foo');
        $this->assertFalse($this->appBuilder->hasComponentBuilder('foo'));
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->assertTrue($this->appBuilder->hasComponentBuilder('foo'));
    }

    public function testHasComponentBuilderWhenUsingProxiedTypeReturnsTrueWhenQueryingProxiedType(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilderProxy::class);
        $componentBuilder->method('getProxiedType')
            ->willReturn('foo');
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->assertTrue($this->appBuilder->hasComponentBuilder('foo'));
    }

    public function testGettingComponentBuilderThrowsExceptionForUnregisteredBuilder(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectErrorMessage('No component builder of type foo found');
        $this->appBuilder->getComponentBuilder('foo');
    }

    public function testGettingComponentBuilderThrowsExceptionWhenCheckingProxiedBuilderTypeDirectly(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilderProxy::class);
        $componentBuilder->method('getProxiedType')
            ->willReturn('foo');
        $this->expectException(OutOfBoundsException::class);
        $this->expectErrorMessage('No component builder of type ' . \get_class($componentBuilder) . ' found');
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->appBuilder->getComponentBuilder(\get_class($componentBuilder));
    }

    public function testGettingComponentBuilderWhenUsingProxiedTypeReturnsProxy(): void
    {
        $componentBuilder = $this->createMock(IComponentBuilderProxy::class);
        $componentBuilder->method('getProxiedType')
            ->willReturn('foo');
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->assertSame($componentBuilder, $this->appBuilder->getComponentBuilder('foo'));
    }
}

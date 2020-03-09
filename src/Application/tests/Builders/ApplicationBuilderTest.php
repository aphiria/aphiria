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
use Aphiria\Application\IModule;
use Aphiria\Application\IComponent;
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
            public function __construct()
            {
                parent::__construct([]);
            }

            public function build(): object
            {
                $this->buildModules();
                $this->buildComponents();

                return $this;
            }
        };
    }

    public function testComponentsAreInitializedInPriorityDescendingOrder(): void
    {
        /**
         * I need to basically duplicate the class definitions here so that each has a unique class name.
         * When I initialize those components, I'm adding them to an array so that I can check the initialization order.
         */
        $initializedComponents = [];
        $lowPriorityComponent = new class($initializedComponents) implements IComponent
        {
            private array $builtComponentsBuilders;

            public function __construct(array &$builtComponentBuilders)
            {
                $this->builtComponentsBuilders = &$builtComponentBuilders;
            }

            public function build(): void
            {
                $this->builtComponentsBuilders[] = $this;
            }
        };
        $highPriorityComponent = new class($initializedComponents) implements IComponent
        {
            private array $builtComponentsBuilders;

            public function __construct(array &$builtComponentBuilders)
            {
                $this->builtComponentsBuilders = &$builtComponentBuilders;
            }

            public function build(): void
            {
                $this->builtComponentsBuilders[] = $this;
            }
        };
        $this->appBuilder->withComponent($lowPriorityComponent, 2);
        $this->appBuilder->withComponent($highPriorityComponent, 1);
        $this->appBuilder->build();
        $this->assertEquals([$highPriorityComponent, $lowPriorityComponent], $initializedComponents);
    }

    public function testComponentsAreInitializedOnBuild(): void
    {
        $component = $this->createMock(IComponent::class);
        $component->expects($this->once())
            ->method('build');
        $this->appBuilder->withComponent($component);
        $this->appBuilder->build();
    }

    public function testModulesAreBuiltOnBuild(): void
    {
        $module = $this->createMock(IModule::class);
        $module->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModule($module);
        $this->appBuilder->build();
    }

    public function testHasComponentReturnsWhetherOrNotComponentIsRegistered(): void
    {
        $component = $this->createMock(IComponent::class);
        $this->assertFalse($this->appBuilder->hasComponent(\get_class($component)));
        $this->appBuilder->withComponent($component);
        $this->assertTrue($this->appBuilder->hasComponent(\get_class($component)));
    }

    public function testGettingComponentThrowsExceptionForUnregistered(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectErrorMessage('No component of type foo found');
        $this->appBuilder->getComponent('foo');
    }
}

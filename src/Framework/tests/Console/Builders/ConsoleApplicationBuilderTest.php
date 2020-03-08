<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IModuleBuilder;
use Aphiria\Application\IBootstrapper;
use Aphiria\Application\IComponent;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Console\Builders\ConsoleApplicationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console application builder
 */
class ConsoleApplicationBuilderTest extends TestCase
{
    private Container $container;
    private ConsoleApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        // To simplify testing, we'll use a real container
        $this->container = new Container();
        $this->appBuilder = new ConsoleApplicationBuilder($this->container, []);
    }

    public function testBuildBindsConsoleApplicationToContainer(): void
    {
        $app = $this->appBuilder->build();
        $this->assertSame($app, $this->container->resolve(ICommandBus::class));
    }

    public function testBuildBootstrapsBootstrappers(): void
    {
        $bootstrapper = $this->createMock(IBootstrapper::class);
        $bootstrapper->expects($this->once())
            ->method('bootstrap');
        $appBuilder = new ConsoleApplicationBuilder($this->container, [$bootstrapper]);
        $appBuilder->build();
    }

    public function testBuildBuildsModulesBeforeComponentsAreInitialized(): void
    {
        $builtParts = [];
        $moduleBuilder = new class($builtParts) implements IModuleBuilder
        {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->builtParts[] = \get_class($this);
            }
        };
        $component = new class($builtParts) implements IComponent
        {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function initialize(): void
            {
                $this->builtParts[] = \get_class($this);
            }
        };
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponent($component);
        $this->appBuilder->withModuleBuilder($moduleBuilder);
        $this->appBuilder->build();
        $this->assertEquals([\get_class($moduleBuilder), \get_class($component)], $builtParts);
    }
}

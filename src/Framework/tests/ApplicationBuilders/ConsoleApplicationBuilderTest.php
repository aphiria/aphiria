<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\ApplicationBuilders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\ApplicationBuilders\IComponentBuilder;
use Aphiria\ApplicationBuilders\IModuleBuilder;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\ApplicationBuilders\ConsoleApplicationBuilder;
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
        $this->appBuilder = new ConsoleApplicationBuilder($this->container);
    }

    public function testBuildBindsConsoleApplicationToContainer(): void
    {
        $app = $this->appBuilder->build();
        $this->assertSame($app, $this->container->resolve(ICommandBus::class));
    }

    public function testBuildBuildsModulesBeforeComponents(): void
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
        $componentBuilder = new class($builtParts) implements IComponentBuilder
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
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->appBuilder->withModuleBuilder($moduleBuilder);
        $this->appBuilder->build();
        $this->assertEquals([\get_class($moduleBuilder), \get_class($componentBuilder)], $builtParts);
    }
}

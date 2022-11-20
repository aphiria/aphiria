<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\UniversalContext;
use Aphiria\Framework\Console\Builders\ConsoleApplicationBuilder;
use Aphiria\Framework\Console\ConsoleApplication;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
        global $argv;
        $actualApp = $this->appBuilder->build();
        $expectedApp = new ConsoleApplication($this->container->resolve(ICommandBus::class), fn (): array => $argv);
        $this->assertEquals($expectedApp, $actualApp);
    }

    public function testBuildBuildsModulesBeforeComponentsAreInitialized(): void
    {
        $builtParts = [];
        $module = new class ($builtParts) implements IModule {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function configure(IApplicationBuilder $appBuilder): void
            {
                $this->builtParts[] = $this::class;
            }
        };
        $component = new class ($builtParts) implements IComponent {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function build(): void
            {
                $this->builtParts[] = $this::class;
            }
        };
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponent($component);
        $this->appBuilder->withModule($module);
        $this->appBuilder->build();
        $this->assertEquals([$module::class, $component::class], $builtParts);
    }

    public function testBuildThatThrowsResolutionExceptionIsRethrown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build the console application');
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willThrowException(new ResolutionException(CommandRegistry::class, new UniversalContext()));
        $appBuilder = new ConsoleApplicationBuilder($container);
        $appBuilder->build();
    }
}

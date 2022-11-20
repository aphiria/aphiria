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
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IServiceResolver;
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
    private Input $input;

    protected function setUp(): void
    {
        // To simplify testing, we'll use a real container
        $this->container = new Container();
        $this->appBuilder = new ConsoleApplicationBuilder($this->container);
        $this->input = new Input('foo');
        $this->container->bindInstance(IServiceResolver::class, $this->container);
        $this->container->bindInstance(Input::class, $this->input);
        $this->container->bindInstance(IOutput::class, $this->createMock(IOutput::class));
    }

    public function testBuildResolvesConsoleApplicationFromServiceResolver(): void
    {
        $app = new ConsoleApplication($this->createMock(ICommandHandler::class), new Input('foo'));
        $this->container->bindInstance(ConsoleApplication::class, $app);
        $this->assertSame($app, $this->appBuilder->build());
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
        $app = new ConsoleApplication($this->createMock(ICommandHandler::class), new Input('foo'));
        $this->container->bindInstance(ConsoleApplication::class, $app);
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
            ->with(ConsoleApplication::class)
            ->willThrowException(new ResolutionException(ConsoleApplication::class, new UniversalContext()));
        $appBuilder = new ConsoleApplicationBuilder($container);
        $appBuilder->build();
    }
}

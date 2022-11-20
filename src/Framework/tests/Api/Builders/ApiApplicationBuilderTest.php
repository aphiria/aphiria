<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Api\Builders\SynchronousApiApplicationBuilder;
use Aphiria\Framework\Api\SynchronousApiApplication;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ApiApplicationBuilderTest extends TestCase
{
    private Container $container;
    private SynchronousApiApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        // To simplify testing, we'll use a real container
        $this->container = new Container();
        // Several tests rely on a request and response writer being bound
        $this->container->bindInstance(IRequest::class, $this->createMock(IRequest::class));
        $this->container->bindInstance(IResponseWriter::class, $this->createMock(IResponseWriter::class));
        $this->appBuilder = new SynchronousApiApplicationBuilder($this->container);
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
        $app = new SynchronousApiApplication(
            $this->createMock(IRequestHandler::class),
            $this->createMock(IRequest::class)
        );
        $this->container->bindInstance(SynchronousApiApplication::class, $app);
        $this->appBuilder->build();
        $this->assertEquals([$module::class, $component::class], $builtParts);
    }

    public function testBuildCreatesApiApplicationFromServiceResolver(): void
    {
        $app = new SynchronousApiApplication($this->createMock(IRequestHandler::class), $this->createMock(IRequest::class));
        $this->container->bindInstance(SynchronousApiApplication::class, $app);
        $this->assertSame($app, $this->appBuilder->build());
    }

    public function testBuildWithoutHavingApiGatewayBoundToContainerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build the API application');
        $this->appBuilder->build();
    }
}

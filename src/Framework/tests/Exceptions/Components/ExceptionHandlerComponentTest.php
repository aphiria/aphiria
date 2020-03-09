<?php
/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Components;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\DependencyInjection\Container;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception handler component
 */
class ExceptionHandlerComponentTest extends TestCase
{
    private ExceptionHandlerComponent $exceptionHandlerComponent;
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;
    private ExceptionResponseFactoryRegistry $exceptionResponseFactories;
    private ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories;
    private MiddlewareComponent $middlewareComponent;

    protected function setUp(): void
    {
        $container = new Container();
        $container->bindInstance(ExceptionResponseFactoryRegistry::class, $this->exceptionResponseFactories = new ExceptionResponseFactoryRegistry());
        $container->bindInstance(ExceptionLogLevelFactoryRegistry::class, $this->exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry());
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
        $this->middlewareComponent = new class() extends MiddlewareComponent
        {
            private array $middleware = [];

            public function __construct()
            {
                // Don't do anything
            }

            public function getMiddleware(): array
            {
                return $this->middleware;
            }

            public function withGlobalMiddleware($middlewareBindings, int $priority = null): self
            {
                $this->middleware[] = $middlewareBindings;

                return $this;
            }
        };
        $this->exceptionHandlerComponent = new ExceptionHandlerComponent($container, $this->appBuilder);
    }

    public function testInitializeWithExceptionHandlerMiddlewareRegistersTheMiddleware(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($this->middlewareComponent);
        $this->exceptionHandlerComponent->withExceptionHandlerMiddleware();
        $this->exceptionHandlerComponent->build();
        $this->assertEquals([new MiddlewareBinding(ExceptionHandler::class)], $this->middlewareComponent->getMiddleware());
    }

    public function testInitializeWithLogLevelFactoryRegistersFactory(): void
    {
        $factory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerComponent->withLogLevelFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertSame($factory, $this->exceptionLogLevelFactories->getFactory(Exception::class));
    }

    public function testInitializeWithResponseFactoryRegistersFactory(): void
    {
        $factory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $this->exceptionHandlerComponent->withResponseFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertSame($factory, $this->exceptionResponseFactories->getFactory(Exception::class));
    }
}

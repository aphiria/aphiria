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
use Aphiria\Exceptions\LogLevelFactoryRegistry;
use Aphiria\Exceptions\Http\ResponseFactoryRegistry;
use Aphiria\Exceptions\Http\Middleware\ExceptionHandler;
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
    private ResponseFactoryRegistry $exceptionResponseFactories;
    private LogLevelFactoryRegistry $exceptionLogLevelFactories;
    private MiddlewareComponent $middlewareComponent;

    protected function setUp(): void
    {
        $container = new Container();
        $container->bindInstance(ResponseFactoryRegistry::class, $this->exceptionResponseFactories = new ResponseFactoryRegistry());
        $container->bindInstance(LogLevelFactoryRegistry::class, $this->exceptionLogLevelFactories = new LogLevelFactoryRegistry());
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

    protected function tearDown(): void
    {
        // Remove the global instance so it doesn't impact other tests
        Container::$globalInstance = null;
    }

    public function testInitializeWithExceptionHandlerMiddlewareRegistersTheMiddleware(): void
    {
        // The Aphiria components use the global instance of the container, so make sure it's set
        Container::$globalInstance = new Container();
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
        $this->exceptionHandlerComponent->withNegotiatedResponseFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertSame($factory, $this->exceptionResponseFactories->getFactory(Exception::class));
    }
}

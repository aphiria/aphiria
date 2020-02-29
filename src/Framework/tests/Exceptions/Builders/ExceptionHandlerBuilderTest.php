<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception handler builder
 */
class ExceptionHandlerBuilderTest extends TestCase
{
    private ExceptionHandlerBuilder $exceptionHandlerBuilder;
    private GlobalExceptionHandler $globalExceptionHandler;
    private ExceptionResponseFactoryRegistry $exceptionResponseFactories;
    private ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories;

    protected function setUp(): void
    {
        $this->globalExceptionHandler = new class() extends GlobalExceptionHandler
        {
            private bool $registerWithPhpCalled = false;

            public function registerWithPhp(): void
            {
                $this->registerWithPhpCalled = true;
            }

            public function wasRegisterWithPhpCalled(): bool
            {
                return $this->registerWithPhpCalled;
            }
        };
        $this->exceptionResponseFactories = new ExceptionResponseFactoryRegistry();
        $this->exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry();
        $this->exceptionHandlerBuilder = new ExceptionHandlerBuilder(
            $this->globalExceptionHandler,
            $this->exceptionResponseFactories,
            $this->exceptionLogLevelFactories
        );
    }

    public function testBuildRegistersGlobalExceptionHandlerMiddleware(): void
    {
        $middlewareBuilder = new class() extends MiddlewareBuilder
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

            public function withGlobalMiddleware($middlewareBindings): MiddlewareBuilder
            {
                $this->middleware[] = $middlewareBindings;

                return $this;
            }
        };
        $appBuilder = $this->createMock(IApplicationBuilder::class);
        $appBuilder->expects($this->once())
            ->method('getComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn($middlewareBuilder);
        $this->exceptionHandlerBuilder->build($appBuilder);
        $this->assertEquals([new MiddlewareBinding(ExceptionHandler::class)], $middlewareBuilder->getMiddleware());
    }

    public function testBuildRegistersGlobalExceptionHandlerWithPhp(): void
    {
        $appBuilder = $this->createMock(IApplicationBuilder::class);
        $appBuilder->expects($this->once())
            ->method('getComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn($this->createMock(MiddlewareBuilder::class));
        $this->assertFalse($this->globalExceptionHandler->wasRegisterWithPhpCalled());
        $this->exceptionHandlerBuilder->build($appBuilder);
        $this->assertTrue($this->globalExceptionHandler->wasRegisterWithPhpCalled());
    }

    public function testWithLogLevelFactoryRegistersFactory(): void
    {
        $factory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerBuilder->withLogLevelFactory(Exception::class, $factory);
        $this->assertSame($factory, $this->exceptionLogLevelFactories->getFactory(Exception::class));
    }

    public function testWithResponseFactoryRegistersFactory(): void
    {
        $factory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $this->exceptionHandlerBuilder->withResponseFactory(Exception::class, $factory);
        $this->assertSame($factory, $this->exceptionResponseFactories->getFactory(Exception::class));
    }
}

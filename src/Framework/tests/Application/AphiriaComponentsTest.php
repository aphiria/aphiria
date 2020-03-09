<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Application;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\AphiriaComponents;
use Aphiria\Framework\Console\Components\CommandComponent;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Framework\Serialization\Components\SerializerComponent;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the Aphiria component trait
 */
class AphiriaComponentsTest extends TestCase
{
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;
    private object $componentBuilder;

    protected function setUp(): void
    {
        $container = $this->createMock(IContainer::class);
        $container->method('resolve')
            ->with(IBinderDispatcher::class)
            ->willReturn($this->createMock(IBinderDispatcher::class));
        $this->componentBuilder = new class($container)
        {
            use AphiriaComponents;
        };
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
    }

    public function testWithBindersRegistersBindersToComponent(): void
    {
        $binder = new class() extends Binder
        {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedComponent = $this->createMock(BinderComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withBinders')
            ->with($binder);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withBinders($this->appBuilder, $binder);
    }

    public function testWithCommandAnnotationsConfiguresComponentToHaveAnnotations(): void
    {
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }

    public function testWithCommandsConfiguresComponentToHaveCommands(): void
    {
        $callback = fn (CommandRegistry $commands) => null;
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withCommands')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withCommands($this->appBuilder, $callback);
    }

    public function testWithEncodersConfiguresComponentToHaveEncoders(): void
    {
        $encoder = $this->createMock(IEncoder::class);
        $expectedComponent = $this->createMock(SerializerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withEncoder')
            ->with('foo', $encoder);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(SerializerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(SerializerComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withEncoder($this->appBuilder, 'foo', $encoder);
    }

    public function testWithExceptionHandlerMiddlewareConfiguresComponentToUseMiddleware(): void
    {
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withExceptionHandlerMiddleware');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withExceptionHandlerMiddleware($this->appBuilder);
    }

    public function testWithExceptionResponseFactoryConfiguresComponentToHaveFactory(): void
    {
        $responseFactory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withResponseFactory')
            ->with(Exception::class, $responseFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withExceptionResponseFactory($this->appBuilder, Exception::class, $responseFactory);
    }

    public function testWithGlobalMiddlewareConfiguresComponentToHaveMiddleware(): void
    {
        $middlewareBinding = new MiddlewareBinding('foo');
        $expectedComponent = $this->createMock(MiddlewareComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withGlobalMiddleware')
            ->with($middlewareBinding, 1);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withGlobalMiddleware($this->appBuilder, $middlewareBinding, 1);
    }

    public function testWithLogLevelFactoryConfiguresComponentToHaveFactory(): void
    {
        $logLevelFactory = fn (Exception $ex) => LogLevel::ALERT;
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withLogLevelFactory')
            ->with(Exception::class, $logLevelFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withLogLevelFactory($this->appBuilder, Exception::class, $logLevelFactory);
    }

    public function testWithObjectConstraintsConfiguresComponentToHaveObjectConstraints(): void
    {
        $callback = fn (ObjectConstraintsRegistry $objectConstraints) => null;
        $expectedComponent = $this->createMock(ValidationComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withObjectConstraints')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withObjectConstraints($this->appBuilder, $callback);
    }

    public function testWithRouteAnnotationsConfiguresComponentToHaveAnnotations(): void
    {
        $expectedComponent = $this->createMock(RouterComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRoutesConfiguresComponentToHaveRoutes(): void
    {
        $callback = fn (RouteBuilderRegistry $routeBuilders) => null;
        $expectedComponent = $this->createMock(RouterComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withRoutes')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withRoutes($this->appBuilder, $callback);
    }

    public function testWithValidatorAnnotationsConfiguresComponentToHaveAnnotations(): void
    {
        $expectedComponent = $this->createMock(ValidationComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withValidatorAnnotations($this->appBuilder);
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Application\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\Builders\AphiriaComponentBuilder;
use Aphiria\Framework\Console\Components\CommandComponent;
use Aphiria\Framework\DependencyInjection\Components\BootstrapperComponent;
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
 * Tests the Aphiria component builder
 */
class AphiriaComponentBuilderTest extends TestCase
{
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;
    private AphiriaComponentBuilder $componentBuilder;

    protected function setUp(): void
    {
        $container = $this->createMock(IContainer::class);
        $container->method('resolve')
            ->with(IBootstrapperDispatcher::class)
            ->willReturn($this->createMock(IBootstrapperDispatcher::class));
        $this->componentBuilder = new AphiriaComponentBuilder($container);
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
    }

    public function testWithBootstrappersRegistersBootstrappersToComponent(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedComponent = $this->createMock(BootstrapperComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withBootstrappers')
            ->with($bootstrapper);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(BootstrapperComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(BootstrapperComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
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
            ->with($middlewareBinding);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($expectedComponent);
        $this->componentBuilder->withGlobalMiddleware($this->appBuilder, $middlewareBinding);
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

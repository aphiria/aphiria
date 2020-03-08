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
        $expectedBootstrapperComponent = $this->createMock(BootstrapperComponent::class);
        $expectedBootstrapperComponent->expects($this->once())
            ->method('withBootstrappers')
            ->with($bootstrapper);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(BootstrapperComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(BootstrapperComponent::class)
            ->willReturn($expectedBootstrapperComponent);
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
    }

    public function testWithCommandAnnotationsConfiguresCommandBuilderToHaveAnnotations(): void
    {
        $expectedCommandBuilder = $this->createMock(CommandComponent::class);
        $expectedCommandBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedCommandBuilder);
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }

    public function testWithCommandsConfiguresCommandBuilderToHaveCommands(): void
    {
        $callback = fn (CommandRegistry $commands) => null;
        $expectedCommandBuilder = $this->createMock(CommandComponent::class);
        $expectedCommandBuilder->expects($this->once())
            ->method('withCommands')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedCommandBuilder);
        $this->componentBuilder->withCommands($this->appBuilder, $callback);
    }

    public function testWithEncodersConfiguresSerializerBuilderToHaveEncoders(): void
    {
        $encoder = $this->createMock(IEncoder::class);
        $expectedSerializerBuilder = $this->createMock(SerializerComponent::class);
        $expectedSerializerBuilder->expects($this->once())
            ->method('withEncoder')
            ->with('foo', $encoder);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(SerializerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(SerializerComponent::class)
            ->willReturn($expectedSerializerBuilder);
        $this->componentBuilder->withEncoder($this->appBuilder, 'foo', $encoder);
    }

    public function testWithExceptionResponseFactoryConfiguresExceptionHandlerBuilderToHaveFactory(): void
    {
        $responseFactory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $expectedExceptionHandlerBuilder = $this->createMock(ExceptionHandlerComponent::class);
        $expectedExceptionHandlerBuilder->expects($this->once())
            ->method('withResponseFactory')
            ->with(Exception::class, $responseFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedExceptionHandlerBuilder);
        $this->componentBuilder->withExceptionResponseFactory($this->appBuilder, Exception::class, $responseFactory);
    }

    public function testWithGlobalMiddlewareConfiguresMiddlewareBuilderToHaveMiddleware(): void
    {
        $middlewareBinding = new MiddlewareBinding('foo');
        $expectedMiddlewareBuilder = $this->createMock(MiddlewareComponent::class);
        $expectedMiddlewareBuilder->expects($this->once())
            ->method('withGlobalMiddleware')
            ->with($middlewareBinding);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($expectedMiddlewareBuilder);
        $this->componentBuilder->withGlobalMiddleware($this->appBuilder, $middlewareBinding);
    }

    public function testWithLogLevelFactoryConfiguresExceptionHandlerBuilderToHaveFactory(): void
    {
        $logLevelFactory = fn (Exception $ex) => LogLevel::ALERT;
        $expectedExceptionHandlerBuilder = $this->createMock(ExceptionHandlerComponent::class);
        $expectedExceptionHandlerBuilder->expects($this->once())
            ->method('withLogLevelFactory')
            ->with(Exception::class, $logLevelFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedExceptionHandlerBuilder);
        $this->componentBuilder->withLogLevelFactory($this->appBuilder, Exception::class, $logLevelFactory);
    }

    public function testWithObjectConstraintsConfiguresValidatorBuilderToHaveObjectConstraints(): void
    {
        $callback = fn (ObjectConstraintsRegistry $objectConstraints) => null;
        $expectedValidatorBuilder = $this->createMock(ValidationComponent::class);
        $expectedValidatorBuilder->expects($this->once())
            ->method('withObjectConstraints')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedValidatorBuilder);
        $this->componentBuilder->withObjectConstraints($this->appBuilder, $callback);
    }

    public function testWithRouteAnnotationsConfiguresRouterBuilderToHaveAnnotations(): void
    {
        $expectedRouterBuilder = $this->createMock(RouterComponent::class);
        $expectedRouterBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedRouterBuilder);
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRoutesConfiguresRouterBuilderToHaveRoutes(): void
    {
        $callback = fn (RouteBuilderRegistry $routeBuilders) => null;
        $expectedRouterBuilder = $this->createMock(RouterComponent::class);
        $expectedRouterBuilder->expects($this->once())
            ->method('withRoutes')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedRouterBuilder);
        $this->componentBuilder->withRoutes($this->appBuilder, $callback);
    }

    public function testWithValidatorAnnotationsConfiguresValidatorBuilderToHaveAnnotations(): void
    {
        $expectedValidatorBuilder = $this->createMock(ValidationComponent::class);
        $expectedValidatorBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedValidatorBuilder);
        $this->componentBuilder->withValidatorAnnotations($this->appBuilder);
    }

    public function testWithValidatorAnnotationsRegistersCorrectComponent(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponent')
            ->with($this->isInstanceOf(ValidationComponent::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($this->createMock(ValidationComponent::class));
        $this->componentBuilder->withValidatorAnnotations($this->appBuilder);
    }
}

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
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\Builders\AphiriaComponentBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilderProxy;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilderProxy;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilderProxy;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilderProxy;
use Aphiria\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Framework\Routing\Builders\RouterBuilderProxy;
use Aphiria\Framework\Serialization\Builders\SerializerBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilderProxy;
use Aphiria\Framework\Validation\Builders\ValidatorBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilderProxy;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
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
    /** @var IContainer|MockObject */
    private IContainer $container;
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;
    private AphiriaComponentBuilder $componentBuilder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->componentBuilder = new AphiriaComponentBuilder($this->container);
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
    }

    public function testWithBootstrappersRegistersBootstrappersToComponentBuilder(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedBootstrapperBuilder = $this->createMock(BootstrapperBuilder::class);
        $expectedBootstrapperBuilder->expects($this->once())
            ->method('withBootstrappers')
            ->with($bootstrapper);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn($expectedBootstrapperBuilder);
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
    }

    public function testWithBootstrappersRegistersCorrectComponentBuilder(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(BootstrapperBuilderProxy::class), 0)
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn($this->createMock(BootstrapperBuilder::class));
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
    }

    public function testWithCommandAnnotationsConfiguresCommandBuilderToHaveAnnotations(): void
    {
        $expectedCommandBuilder = $this->createMock(CommandBuilder::class);
        $expectedCommandBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($expectedCommandBuilder);
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }

    public function testWithCommandAnnotationsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(CommandBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($this->createMock(CommandBuilder::class));
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(false);
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }

    public function testWithCommandsConfiguresCommandBuilderToHaveCommands(): void
    {
        $callback = fn (CommandRegistry $commands) => null;
        $expectedCommandBuilder = $this->createMock(CommandBuilder::class);
        $expectedCommandBuilder->expects($this->once())
            ->method('withCommands')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($expectedCommandBuilder);
        $this->componentBuilder->withCommands($this->appBuilder, $callback);
    }

    public function testWithCommandsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(CommandBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($this->createMock(CommandBuilder::class));
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(false);
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $this->componentBuilder->withCommands($this->appBuilder, fn (CommandRegistry $commands) => null);
    }

    public function testWithEncodersConfiguresSerializerBuilderToHaveEncoders(): void
    {
        $encoder = $this->createMock(IEncoder::class);
        $expectedSerializerBuilder = $this->createMock(SerializerBuilder::class);
        $expectedSerializerBuilder->expects($this->once())
            ->method('withEncoder')
            ->with('foo', $encoder);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(SerializerBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(SerializerBuilder::class)
            ->willReturn($expectedSerializerBuilder);
        $this->componentBuilder->withEncoder($this->appBuilder, 'foo', $encoder);
    }

    public function testWithEncodersRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(SerializerBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(SerializerBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(SerializerBuilder::class)
            ->willReturn($this->createMock(SerializerBuilder::class));
        $this->componentBuilder->withEncoder($this->appBuilder, 'foo', $this->createMock(IEncoder::class));
    }

    public function testWithExceptionResponseFactoryConfiguresExceptionHandlerBuilderToHaveFactory(): void
    {
        $responseFactory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $expectedExceptionHandlerBuilder = $this->createMock(ExceptionHandlerBuilder::class);
        $expectedExceptionHandlerBuilder->expects($this->once())
            ->method('withResponseFactory')
            ->with(Exception::class, $responseFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn($expectedExceptionHandlerBuilder);
        $this->componentBuilder->withExceptionResponseFactory($this->appBuilder, Exception::class, $responseFactory);
    }

    public function testWithExceptionResponseFactoryRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(ExceptionHandlerBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn($this->createMock(ExceptionHandlerBuilder::class));
        $this->componentBuilder->withExceptionResponseFactory($this->appBuilder, Exception::class, fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class));
    }

    public function testWithGlobalMiddlewareConfiguresMiddlewareBuilderToHaveMiddleware(): void
    {
        $middlewareBinding = new MiddlewareBinding('foo');
        $expectedMiddlewareBuilder = $this->createMock(MiddlewareBuilder::class);
        $expectedMiddlewareBuilder->expects($this->once())
            ->method('withGlobalMiddleware')
            ->with($middlewareBinding);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn($expectedMiddlewareBuilder);
        $this->componentBuilder->withGlobalMiddleware($this->appBuilder, $middlewareBinding);
    }

    public function testWithGlobalMiddlewareRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(MiddlewareBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(MiddlewareBuilder::class)
            ->willReturn($this->createMock(MiddlewareBuilder::class));
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(MiddlewareCollection::class)
            ->willReturn(false);
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(MiddlewareCollection::class, $this->isInstanceOf(MiddlewareCollection::class));
        $this->componentBuilder->withGlobalMiddleware($this->appBuilder, new MiddlewareBinding('foo'));
    }

    public function testWithLogLevelFactoryConfiguresExceptionHandlerBuilderToHaveFactory(): void
    {
        $logLevelFactory = fn (Exception $ex) => LogLevel::ALERT;
        $expectedExceptionHandlerBuilder = $this->createMock(ExceptionHandlerBuilder::class);
        $expectedExceptionHandlerBuilder->expects($this->once())
            ->method('withLogLevelFactory')
            ->with(Exception::class, $logLevelFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn($expectedExceptionHandlerBuilder);
        $this->componentBuilder->withLogLevelFactory($this->appBuilder, Exception::class, $logLevelFactory);
    }

    public function testWithLogLevelFactoryRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(ExceptionHandlerBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(ExceptionHandlerBuilder::class)
            ->willReturn($this->createMock(ExceptionHandlerBuilder::class));
        $this->componentBuilder->withLogLevelFactory($this->appBuilder, Exception::class, fn (Exception $ex) => LogLevel::ALERT);
    }

    public function testWithObjectConstraintsConfiguresValidatorBuilderToHaveObjectConstraints(): void
    {
        $callback = fn (ObjectConstraintsRegistry $objectConstraints) => null;
        $expectedValidatorBuilder = $this->createMock(ValidatorBuilder::class);
        $expectedValidatorBuilder->expects($this->once())
            ->method('withObjectConstraints')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn($expectedValidatorBuilder);
        $this->componentBuilder->withObjectConstraints($this->appBuilder, $callback);
    }

    public function testWithObjectConstraintsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(ValidatorBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn($this->createMock(ValidatorBuilder::class));
        $this->componentBuilder->withObjectConstraints($this->appBuilder, fn (ObjectConstraintsRegistry $objectConstraints) => null);
    }

    public function testWithRouteAnnotationsConfiguresRouterBuilderToHaveAnnotations(): void
    {
        $expectedRouterBuilder = $this->createMock(RouterBuilder::class);
        $expectedRouterBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn($expectedRouterBuilder);
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRouteAnnotationsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(RouterBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn($this->createMock(RouterBuilder::class));
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRoutesConfiguresRouterBuilderToHaveRoutes(): void
    {
        $callback = fn (RouteBuilderRegistry $routeBuilders) => null;
        $expectedRouterBuilder = $this->createMock(RouterBuilder::class);
        $expectedRouterBuilder->expects($this->once())
            ->method('withRoutes')
            ->with($callback);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn($expectedRouterBuilder);
        $this->componentBuilder->withRoutes($this->appBuilder, $callback);
    }

    public function testWithRoutesRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(RouterBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(RouterBuilder::class)
            ->willReturn($this->createMock(RouterBuilder::class));
        $this->componentBuilder->withRoutes($this->appBuilder, fn (RouteBuilderRegistry $routeBuilders) => null);
    }

    public function testWithValidatorAnnotationsConfiguresValidatorBuilderToHaveAnnotations(): void
    {
        $expectedValidatorBuilder = $this->createMock(ValidatorBuilder::class);
        $expectedValidatorBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn($expectedValidatorBuilder);
        $this->componentBuilder->withValidatorAnnotations($this->appBuilder);
    }

    public function testWithValidatorAnnotationsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(ValidatorBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(ValidatorBuilder::class)
            ->willReturn($this->createMock(ValidatorBuilder::class));
        $this->componentBuilder->withValidatorAnnotations($this->appBuilder);
    }
}

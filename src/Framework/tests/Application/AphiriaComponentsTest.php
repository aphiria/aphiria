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
use Aphiria\Application\IModule;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Binders\Binder;
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
use Closure;
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

    protected function setUp(): void
    {
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
    }

    public function testWithBindersRegistersBindersToComponent(): void
    {
        $binder = new class() extends Binder {
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Binder $binder): void
            {
                $this->withBinders($appBuilder, $binder);
            }
        };
        $component->build($this->appBuilder, $binder);
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withCommandAnnotations($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithCommandsConfiguresComponentToHaveCommands(): void
    {
        $callback = fn(CommandRegistry $commands) => null;
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withCommands($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
    }

    public function testWithConsoleExceptionOutputWriterConfiguresComponentToHaveWriter(): void
    {
        $outputWriter = function (Exception $ex, IOutput $output) {
            $output->writeln('foo');

            return 1;
        };
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withConsoleOutputWriter')
            ->with(Exception::class, $outputWriter);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, string $exceptionType, Closure $callback): void
            {
                $this->withConsoleExceptionOutputWriter($appBuilder, $exceptionType, $callback);
            }
        };
        $component->build($this->appBuilder, Exception::class, $outputWriter);
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, string $type, IEncoder $encoder): void
            {
                $this->withEncoder($appBuilder, $type, $encoder);
            }
        };
        $component->build($this->appBuilder, 'foo', $encoder);
    }

    public function testWithHttpResponseFactoryConfiguresComponentToHaveFactory(): void
    {
        $responseFactory = fn(Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withHttpResponseFactory')
            ->with(Exception::class, $responseFactory);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(
                IApplicationBuilder $appBuilder,
                string $exceptionType,
                Closure $responseFactory
            ): void {
                $this->withHttpExceptionResponseFactory($appBuilder, $exceptionType, $responseFactory);
            }
        };
        $component->build($this->appBuilder, Exception::class, $responseFactory);
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
        $component = new class() {
            use AphiriaComponents;

            public function build(
                IApplicationBuilder $appBuilder,
                MiddlewareBinding $middlewareBinding,
                int $priority
            ): void {
                $this->withGlobalMiddleware($appBuilder, $middlewareBinding, $priority);
            }
        };
        $component->build($this->appBuilder, $middlewareBinding, 1);
    }

    public function testWithLogLevelFactoryConfiguresComponentToHaveFactory(): void
    {
        $logLevelFactory = fn(Exception $ex) => LogLevel::ALERT;
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
        $component = new class() {
            use AphiriaComponents;

            public function build(
                IApplicationBuilder $appBuilder,
                string $exceptionType,
                Closure $logLevelFactory
            ): void {
                $this->withLogLevelFactory($appBuilder, $exceptionType, $logLevelFactory);
            }
        };
        $component->build($this->appBuilder, Exception::class, $logLevelFactory);
    }

    public function testWithModulesAddsMultipleModulesToAppBuilder(): void
    {
        $modules = [$this->createMock(IModule::class), $this->createMock(IModule::class)];
        $this->appBuilder->expects($this->at(0))
            ->method('withModule')
            ->with($modules[0]);
        $this->appBuilder->expects($this->at(1))
            ->method('withModule')
            ->with($modules[1]);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, array $modules): void
            {
                $this->withModules($appBuilder, $modules);
            }
        };
        $component->build($this->appBuilder, $modules);
    }

    public function testWithModulesAddsSingleModuleToAppBuilder(): void
    {
        $module = $this->createMock(IModule::class);
        $this->appBuilder->expects($this->once())
            ->method('withModule')
            ->with($module);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IModule $module): void
            {
                $this->withModules($appBuilder, $module);
            }
        };
        $component->build($this->appBuilder, $module);
    }

    public function testWithObjectConstraintsConfiguresComponentToHaveObjectConstraints(): void
    {
        $callback = fn(ObjectConstraintsRegistry $objectConstraints) => null;
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withObjectConstraints($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withRouteAnnotations($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithRoutesConfiguresComponentToHaveRoutes(): void
    {
        $callback = fn(RouteBuilderRegistry $routeBuilders) => null;
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withRoutes($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
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
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withValidatorAnnotations($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }
}
